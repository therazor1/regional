<?php namespace Controllers\admin;

use Inc\Auth;
use Inc\Mailer;
use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Libs\Pixie\Raw;
use Models\FlujoAprobacion;
use Models\Log;
use Models\Management;
use Models\Obra;
use Models\Role;
use Models\Session;
use Models\TipoDocumento;
use Models\User;
use Models\UserObra;

class users extends _controller
{
    protected $no_see = ['item'];

    function index(Req $req)
    {
        // $req = $this->request->getVar(); //capturar datos del frontend
        $fil = $req->fil([
            'id_role'       => $req->num('id_role'),
        ]);

        $qb = QB::table('users us');
        $qb->select([
            'us.*',
            'ro.name'                       => 'ro_name',
        ]);
        $qb->leftJoin('roles ro', 'ro.id', '=', 'us.id_role');
        $qb->where('us.id_type_user', '=', User::TYPE_OPERATOR);
        $qb->where('us.state', '!=', User::_STATE_DELETED);

        if ($fil->id)
            $qb->where('us.id', $fil->id);

        if ($fil->id_role)
            $qb->where('ro.id', $fil->id_role);

        //BUSCADOR
        if ($fil->query)
            $qb->whereLike('CONCAT(us.name,us.surname,us.email,us.document)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(us.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {

            //Datos a expotar
            if ($fil->export) return [
                 //Esto es para los filtrosss -> nombre col
                'ID'       => $o->id,
                'Nombre'   => $o->name,
                'Apellido' => $o->surname,
                'Email'    => $o->email,
                'Fecha'    => Util::humanDatetime($o->date_created),
            ];

            unset($o->password);

            return $o; //devuelve todo -> los con y sin filtros
        });
    }

    function form(Req $req)
    {
        $rsp = Rsp::ok();

        if ($id = $req->id()) {
            $item = User::findOrNull($id);
            if ($item) {

                $rsp->setItem($item);

            } else {
                return Rsp::e404();
            }
        }

        $rsp->set('roles', Role::byTypeUser(User::TYPE_OPERATOR));

        return $rsp;
    }

    function create(Req $req)
    {
        $type = User::TYPE_OPERATOR;
        //Al momento de crear estan las validaciones desde el backend
        $data = $req->data([
            'id'            => 'id',
            'id_role'       => ['required|id' => 'Perfil'],
            'name'          => 'required',
            'surname'       => 'required',
            'email'         => 'required|email',
            'password'      => 'default:',
            'phone'         => 'required',
        ]);

        if (User::has($type, $data->id, 'email', $data->email)) {
            return rsp('El correo electrónico ya está en uso.');
        }

        $item = User::find($data->id);
        $item->data('id_type_user', $type);
        $item->data('id_role', $data->id_role);
        $item->data('name', $data->name);
        $item->data('surname', $data->surname);
        $item->data('email', $data->email);
        $item->data('phone', $data->phone);

        if ($data->password) {
            $item->data('password', md5($data->password));
        }

        return $item->saveRSP();
    }

    function autocomplete(Req $req)
    {
        if ($value = $req->num('value')) {

            $item = User::find($value);

            return Rsp::item([
                'value' => $item->id,
                'label' => $item->name . ' ' . $item->surname,
            ]);

        } else {
            $query = $req->any('query');
            $id_type_user = $req->num('id_type_user');
            $state = $req->num('state', -1);

            $qb = QB::table('users us');
            $qb->select(
                'us.*',
                'tu.name tu_name');
            $qb->join('type_users tu', 'tu.id', '=', 'us.id_type_user');
            $qb->where('us.state', '!=', User::_STATE_DELETED);
            $qb->where('CONCAT(us.name,us.surname,us.email,us.phone,us.document)',
                'LIKE', '%' . str_replace(' ', '%', $query) . '%');
            if ($id_type_user)
                $qb->where('us.id_type_user', $id_type_user);
            if ($state >= 0)
                $qb->where('us.state', $state);
            $qb->orderBy('us.name');
            $qb->limit(5);

            return Rsp::items($qb->get());
        }
    }

    function loginAs(Req $req)
    {
        $data = $req->data(['id' => 'required|id']);

        $user = User::find($data->id);

        if (!Auth::root()) {
            return rsp('No tienes permisos suficientes.');

        } else if (!$user->exist()) {
            return Rsp::e404();

        } else {
            $uuid = 'login_as:by:' . Auth::id();

            $session = Session::get($uuid, $user->id);
            $session->data('platform', 'web');
            $session->data('uuid', $uuid);
            $session->data('app_version', stg('cms_version'));
            $session->data('state', Session::_STATE_ENABLED);

            if (!$session->exist()) {
                $session->data('id_user', $user->id);
                $session->data('token', Util::token($user->id));
            }

            if ($session->save()) {

                Log::addMe(43, $user->id, 'users');

                return Rsp::ok()->set('token', $session->token);
            } else {
                return Rsp::e500();
            }
        }

    }

    function enable(Req $req)
    {
        return User::enableRSP($req->requiredId());
    }

    function disable(Req $req)
    {
        return User::disableRSP($req->requiredId());
    }

    function remove(Req $req)
    {
        $datas = $req->data([
            'id' => 'required|id'
        ]);
        //search user ID
        $item = User::find($datas->id);

        if (!$item->exist()) {
            return Rsp::e404();

        } else if ($item->state == User::_STATE_DELETED) {
            return rsp('Ya fue eliminado previamente');

        } else if ($item->save([
            'email'        => 'removed:' . $item->email,
            'state'        => User::_STATE_DELETED,
            'date_deleted' => Raw::now(),
        ])) {

            Log::addMe(Log::DELETE, $item->id, 'users');

            return Rsp::ok();
        } else {
            return Rsp::e500();
        }
    }

}
