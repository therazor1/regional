<?php namespace Controllers\admin;

use Inc\Auth;
use Inc\Mailer;
use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Empresa;
use Models\FlujoAprobacion;
use Models\Log;
use Models\Obra;
use Models\Role;
use Models\TipoDocumento;
use Models\User;
use Models\UserObra;

class usuarios extends users
{
    public function index(Req $req)
    {
        $fil = $req->fil([
            'id_obra'         => $req->num('id_obra'),
            'id_role'         => $req->num('id_role'),
            'not_ohl_central' => $req->bool('not_ohl_central'),
        ]);

        $qb = QB::table('users us');
        $qb->select([
            'us.*',
            'td.nombre'    => 'td_nombre',
            'ro.name'      => 'ro_name',
            'COUNT(uo.id)' => 'num_user_obras',
        ]);
        $qb->leftJoin('tipo_documentos td', 'td.id', '=', 'us.id_tipo_documento');
        $qb->leftJoin('user_obras uo', 'uo.id_user', '=', 'us.id');
        $qb->leftJoin('roles ro', 'ro.id', 'in', '(uo.id_roles)');
        $qb->where('us.id_type_user', '=', User::TYPE_OPERATOR);
        $qb->where('us.state', '!=', Role::_STATE_DELETED);

        if ($fil->id)
            $qb->where('us.id', $fil->id);

        if ($fil->id_obra)
            $qb->where('uo.id_obra', $fil->id_obra);

        if ($fil->not_ohl_central)
            $qb->where('us.ohl_central', 0);

        if ($fil->id_role)
            $qb->where('ro.id', $fil->id_role);

        if ($fil->query)
            $qb->whereLike('CONCAT(us.name,us.surname,us.email,us.document)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(us.date_created)', $fil->date_from, $fil->date_to);

        $qb->groupBy('us.id');

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'ID'        => $o->id,
                'Nombre'    => $o->name,
                'Apellido'  => $o->surname,
                'Email'     => $o->email,
                'Documento' => $o->document,
                'Fecha'     => Util::humanDatetime($o->date_created),
            ];

            unset($o->password);

            $o->user_obras = QB::table('user_obras uo')
                ->select([
                    'uo.*',

                    'ob.nombre' => 'ob_nombre',
                    'em.nombre' => 'em_nombre',

                    #'ro.name'      => 'ro_name',
                    #'ro.aprobador' => 'ro_aprobador',

                    'fl.nombre' => 'fl_nombre',
                    'fl.monto'  => 'fl_monto',
                ])
                ->join('obras ob', 'ob.id', '=', 'uo.id_obra')
                ->join('empresas em', 'em.id', '=', 'ob.id_empresa')
                #->join('roles ro', 'ro.id', '=', 'uo.id_role')
                ->leftJoin('flujo_aprobaciones fl', 'fl.id', '=', 'uo.id_flujo_aprobacion')
                ->where('uo.id_user', $o->id)
                ->orderBy('ob.nombre')
                ->get();

            foreach ($o->user_obras as $uo) {
                $id_roles = explod($uo->id_roles);
                if ($id_roles) {
                    $uo->roles = QB::table('roles')->whereIn('id', $id_roles)->get();
                    foreach ($uo->roles as $role) {
                        if ($role->name == 'Root' && !Auth::root()) {
                            $o->protected = true;
                        }

                        if ($role->aprobador == '1') {
                            $role->name .= ' (' . coin($uo->fl_monto) . ')';
                        }
                    }
                } else {
                    $uo->roles = [];
                }
            }

            return $o;
        });
    }

    public function create(Req $req)
    {
        $type = User::TYPE_OPERATOR;

        $data = $req->data([
            'id'                               => 'id',
            #'id_role'                          => ['required_if:ohl_central,1|id' => 'Perfil'],
            'id_roles'                         => ['required_if:ohl_central,1|array|min:1' => 'Perfiles'],
            'id_roles.*'                       => 'required|id',
            'id_tipo_documento'                => ['required|id' => 'Tipo de documento'],
            'name'                             => 'required',
            'surname'                          => 'required',
            'document'                         => 'required',
            'email'                            => 'default:',
            'tipo_trabajo'                     => 'default:',
            'fecha_ingreso'                    => 'default:|date',
            'fecha_salida'                     => 'default:|date',
            'ohl_central'                      => 'bool',
            # obras, solo recibiremos las obras nuevas agregadas, las que no tienen id
            'user_obras'                       => 'array',
            'user_obras.*.id_obra'             => 'required|id',
            #'user_obras.*.id_role'             => 'required|id',
            'user_obras.*.id_roles'            => 'required|array|min:1',
            'user_obras.*.id_roles.*'          => 'required|id',
            'user_obras.*.id_flujo_aprobacion' => 'id',
        ]);

        $tipo_documento = TipoDocumento::find($data->id_tipo_documento);

        if ($tipo_documento->nombre == 'RUT') {
            if (!validaRUT($data->document)) {
                return rsp('El RUT no es válido.');
            }
        }

        if (strlen($data->document) < $tipo_documento->min_digitos) {
            return rsp('El Nº Documento debe tener al menos ' . $tipo_documento->min_digitos . ' dígitos.');

        } else if (strlen($data->document) > $tipo_documento->max_digitos) {
            return rsp('El Nº Documento debe tener un máximo de ' . $tipo_documento->max_digitos . ' dígitos.');

        } else if (User::has($type, $data->id, 'email', $data->email)) {
            return rsp('El correo electrónico ya está en uso.');

        }

        $cambio_perfil = false;
        $id_roles_anterior = [];

        $item = User::find($data->id);
        $item->data('id_type_user', $type);

        if ($data->ohl_central) {
            $id_roles_anterior = explod($item->id_roles);
            $cambio_perfil = (count($id_roles_anterior) != count($data->id_roles));
            $item->data('id_roles', implode(',', $data->id_roles));
        }

        $item->data('id_tipo_documento', $data->id_tipo_documento);
        $item->data('document', $data->document);
        $item->data('name', $data->name);
        $item->data('surname', $data->surname);
        $item->data('email', $data->email);
        $item->data('tipo_trabajo', $data->tipo_trabajo);
        $item->data('fecha_ingreso', $data->fecha_ingreso);
        $item->data('fecha_salida', $data->fecha_salida);
        $item->data('ohl_central', $data->ohl_central);
        $item->dataPic('pic_firma');

        # validar que obra non se repita y que exista los objetos obra/role
        if (!$data->ohl_central) {
            if ($data->user_obras) {
                # las obras no estan vacia, validamos su contenido
                foreach ($data->user_obras as $i1 => $o1) {
                    $prefijo = 'Obra ' . ($i1 + 1) . ': ';

                    $obra = Obra::find($o1['id_obra']);
                    //$role = Role::find($o1['id_role']);
                    $nivel = FlujoAprobacion::find($o1['id_flujo_aprobacion']);

                    if (!Util::isEmail($data->email))
                        return rsp('El campo Coreo electrónico es requerido.');

                    # validar existencia
                    if (!$obra->exist())
                        return rsp($prefijo . 'Obra no existe.');

                    /*if (!$role->exist())
                        return rsp($prefijo . 'Perfil no existe.');*/

                    /*if ($role->aprobador == '1' && !$nivel->exist())
                        return rsp($prefijo . 'Nivel no existe.');*/

                    if ($item->exist()
                        && QB::table('user_obras')
                            ->where('id_user', $item->id)
                            ->where('id_obra', $obra->id)
                            ->count() > 0)
                        return rsp($prefijo . 'Ya lo agregaste anteriormente.');

                    # validar que no se repitan
                    foreach ($data->user_obras as $i2 => $o2) {
                        if ($i2 != $i1 && $o2['id_obra'] == $o1['id_obra']) {
                            return rsp($prefijo . 'Se repite la obra.');
                        }
                    }
                }

            } else if (!$item->exist() || QB::table('user_obras')->where('id_user', $item->id)->count() == 0) {
                # las obras estan vacias, no permitir si es nuevo el usuario o si no tiene agregados
                return rsp('Debe asignar al menos una obra por usuario.');
            }
        }

        $rsp = $item->saveRSP();

        if ($rsp->ok) {
            if ($data->ohl_central) {
                # eliminar cualquier obra que se haya asignado a este usuari xq ya es ohl central
                UserObra::where('id_user', $item->id)->delete();
            } else {
                # guardamos los user_obras, en este punto solo insertamos, hay posibilidad de modificarlo desde el front
                if ($data->user_obras) {
                    foreach ($data->user_obras as $uo) {
                        UserObra::insert([
                            'id_user'             => $item->id,
                            'id_obra'             => $uo['id_obra'],
                            #'id_role'             => $uo['id_role'],
                            'id_roles'            => implode(',', $uo['id_roles']),
                            'id_flujo_aprobacion' => $uo['id_flujo_aprobacion'],
                        ]);
                    }

                    Mailer::usuarioCambioPerfil($item->name, $item->email);
                    $item->logMe(19, [
                        'perfilles_agregados' => array_map(function ($uo) {
                            $obra = Obra::find($uo['id_obra']);
                            #$role = Role::find($uo['id_role']);
                            return [
                                #'perfil' => $role->name,
                                'obra' => $obra->nombre,
                            ];
                        }, $data->user_obras),
                    ]);

                }
            }
            if ($rsp->is_new) {
                $item->correoUsuarioCreado();
            } else {
                # revisar si se cambio de contraseña
                if ($cambio_perfil) {
                    Mailer::usuarioCambioPerfil($item->name, $item->email);
                    $item->logMe(19, [
                        'perfil_anterior' => $id_roles_anterior,
                        'perfil_nuevo'    => $data->id_roles,
                    ]);
                }
            }
        }

        return $rsp;
    }

    public function form(Req $req)
    {
        $rsp = Rsp::ok();

        if ($id = $req->id()) {
            $item = User::findOrNull($id);
            if ($item) {

                if ($item->ohl_central) {
                    $item->id_roles = explod($item->id_roles);
                } else {
                    $item->id_roles = [];
                }

                $rsp->setItem($item);

                $user_obras = QB::table('user_obras uo')
                    ->select(
                        'uo.*',
                        'ob.nombre ob_nombre',
                        'em.nombre em_nombre'
                    #'ro.name ro_name'
                    )
                    ->join('obras ob', 'ob.id', '=', 'uo.id_obra')
                    ->join('empresas em', 'em.id', '=', 'ob.id_empresa')
                    #->join('roles ro', 'ro.id', '=', 'uo.id_role')
                    ->where('uo.id_user', $item->id)
                    ->get();

                foreach ($user_obras as $user_obra) {
                    $id_roles = explod($user_obra->id_roles);
                    if ($id_roles) {
                        $user_obra->roles = QB::table('roles')->whereIn('id', $id_roles)->get();
                    } else {
                        $user_obra->roles = [];
                    }
                }

                $rsp->set('user_obras', $user_obras);

            } else {
                return Rsp::e404();
            }
        }

        $rsp->set('tipo_documentos', array_map(function ($o) {
            $o->min_digitos = (int)$o->min_digitos;
            $o->max_digitos = (int)$o->max_digitos;
            return $o;
        }, TipoDocumento::all()));

        $rsp->set('roles', Role::where('id_type_user', User::TYPE_OPERATOR)
            ->where('state', Role::_STATE_ENABLED)
            ->where('aprobador', 0) # no podemos agregar usuario OHL Central como aprobador
            ->get());

        return $rsp;
    }

    private function _roles()
    {
        $qb = QB::table('roles');
        $qb->where('state', Role::_STATE_ENABLED);
        $qb->where('id_type_user', User::TYPE_OPERATOR);

        if (!Auth::root())
            $qb->where('id', '!=', Role::ID_ROOT);

        $qb->orderBy(Role::ORDER_BY);

        return $qb->get();
    }

    public function roles()
    {
        $roles = QB::table('roles')
            ->where('state', Role::_STATE_ENABLED)
            ->where('id_type_user', User::TYPE_OPERATOR)
            ->orderBy(Role::ORDER_BY)
            ->get();
        return Rsp::items($roles);
    }

    public function enable(Req $req)
    {

        $item = User::find($req->id());
        $item->data(User::STATE, User::_STATE_ENABLED);

        if (!$item->exist()) {
            return Rsp::e404();

        } else if ($item->update()) {

            Log::addMe(Log::ENABLE, $item->id, 'users');
            $item->correoUsuarioCreado();

            return Rsp::ok();
        } else {
            return Rsp::e500();
        }
    }
}
