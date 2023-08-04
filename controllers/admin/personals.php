<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Inc\Auth;
use Libs\Pixie\QB;
use Models\Client;
use Models\Dia;
use Models\Empresa;
use Models\Fitbit;
use Models\Personal;
use Models\Role;
use Models\User;

class personals extends _controller
{
    protected $no_see = ['index'];

    //=====SELECT DB=====
    /**
     *index (renderizar data en table -> recibe por get filtros (busqueda + rangos de fecha))
     *
     * Inc\Fil Object
    (
    [start] =>
    [length] =>
    [order_col] => id
    [order_dir] => DESC
    [date_from] =>
    [date_to] =>
    [id] =>
    [query] => hola (search)
    [state] => -1
    [export] =>
    [value] => 0
    [page] => 0
    [limit] => 100
    )
     */
    function index(Req $req)
    {
        $fil = $req->fil();
        $qb = QB::table('users us');
        $qb->select([
            'us.*',
            'ro.name rol',
            'cl.name cliente',
            'em.name empresa',
        ]);
        $qb->leftJoin('roles ro', 'ro.id', '=', 'us.id_role');
        $qb->leftJoin('clients cl', 'cl.id', '=', 'us.id_client');
        $qb->leftJoin('empresas em', 'em.id', '=', 'us.id_emp_transporte');
        $qb->where('us.state', '!=', Personal::_STATE_DELETED);
        $qb->where('us.id_type_user', '=', User::TYPE_COLABORADOR);

        if ($fil->id)
            $qb->where('us.id', $fil->id);

        //buscador ->obligatorio
        if ($fil->query)
            //-> Ojo poner solo atributos de la tabla principal para q funcione el filtro
            $qb->whereLike('CONCAT(us.name,us.surname,us.email,us.phone,us.id_role,us.escolta,us.id_emp_transporte)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(us.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'Estado laboral'        => ($o->state_laboral) ? 'ACTIVO' : 'INACTIVO',
                'Nombre'                => $o->name,
                'Apellido'              => $o->surname,
                'Email'                 => $o->email,
                'Teléfono'              => $o->phone,
                'Cargo'                 => $o->rol,
                'Empresa de transporte' => $o->empresa,
                'Fecha de registro'     => Util::humanDatetime($o->date_created),
            ];
            //Aquí antes de enviar -> puedes hacer condicionales
            $o->escolta_obj   = User::escoltaObj($o->escolta);
            $o->state_laboral = User::state_laboraObj($o->state_laboral);

            return $o;
        });
    }

    // filtrar personal pero retornara el costo segun capacidades
    // se maneja en ruta diferetne por el consumo que representa para cada necesidad
    // aqui no habra paginacion, ya que se obliga agregar un filtro

    //=====INSERT DB=====
    public function create(Req $req)
    {
        $data = $req->data([
            'id'                => 'id',
            'id_role'           => ['required' => 'rol'],
            'id_emp_transporte' => ['required' =>'empresa de transporte'],
            'name'              => ['required' => 'nombre'],
            'surname'           => ['required' => 'apellido'],
            'document'           => ['required' => 'DNI'],
            //'email'             => 'required|email',
            'email'         => 'default:',
            'phone'             => 'default:',
            'password'          => 'default:',
            'id_fitbit'         => 'default:',
            'state_laboral'     => 'default:',
        ]);

        $name=$data->name;
        $surname=$data->surname;
        if (User::existNameSurname($name,$surname, $data->id))
            return rsp('El nombre y apellido ya está en uso.');

        if(User::existIdFitbit($data->id_fitbit, $data->id)){
            return rsp("El id fitbit ya está en uso");
        }
        if(!Fitbit::validarIdFitbit($data->id_fitbit)){
            return rsp("No es un ID de Fitbit válido");
        }
        

        //TODO: insert $data->id = 0 | $data->id !=0 update -> < default: >
        $item = User::find($data->id);
        $item->data('id_type_user', User::TYPE_COLABORADOR);
        $item->data('id_role', $data->id_role);
        //$item->data('id_client', $data->id_client);
        $item->data('id_emp_transporte', $data->id_emp_transporte);
        $item->data('name', $data->name);
        $item->data('surname', $data->surname);
        $item->data('document', $data->document);
        $item->data('email', $data->email);
        $item->data('phone', $data->phone);
        $item->data('id_fitbit', $data->id_fitbit);

        //capturamos y encriptamos el password
        if ($data->password) {
            $item->data('password', md5($data->password));
        }
        $item->data('state_laboral', $data->state_laboral);

        if ($item->save()) {
            //logica dias de trabajo
            QB::table('usuario_dias')->where('id_conductor', $item->id)->delete();
            $usuario_dias = $req->arr('usuario_dias');

            foreach ($usuario_dias as $usuario_dia) {
                QB::table('usuario_dias')->insert([
                    'id_conductor' => $item->id,
                    'id_dia'       => $usuario_dia
                ]);
            }
            return Rsp::ok();
        }

        return rsp();
    }

    //=====UPPATE DB=====
    public function form(Req $req)
    {
        //con esto saber el id del registro -> sino no funciona el edit
        $item               = User::find($req->num('id'));
        $usuario_dias       = QB::table('usuario_dias')->where('id_conductor', $item->id)->get();
        $item->usuario_dias = Util::getArrValue($usuario_dias, 'id_dia');

        $rsp = rsp(true)->set('item', $item);

        if ($item->id_emp_transporte) {
            $empresa=QB::table('empresas')
                ->select('id, name')
                ->where('id',$item->id_emp_transporte)
                ->first();

            $rsp->set('empresa', [
                'label' => $empresa->name,
                'value' => $empresa->id,
            ]);
        }

        $rsp->set('roles', Role::where('id_type_user', User::TYPE_COLABORADOR)->select('id', 'name')->get());
        $rsp->set('empresas', Empresa::all());
        $rsp->set('state_laboral_select',[
            pair(1, 'ACTIVO'),
            pair(0, 'INACTIVO'),
        ]);
        $rsp->set('dias',Dia::all());

        return $rsp;
    }

    public function remove(Req $req)
    {
        return User::deleteRSP($req->num('id'));
    }

    public function importar()
    {
        return Rsp::ok()->set('url_formato', _PATH_ . '/uploads/logs/ots.txt');
    }

    public function verificar()
    {
        return Rsp::item([])->set('columnas', [par('', '')]);
    }

}
