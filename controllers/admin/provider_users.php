<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Role;
use Models\User;

class provider_users extends users
{
    public $modules = ['proveedores'];

    public function index(Req $req)
    {
        $fil = $req->fil([
            'id_proveedor' => $req->num('id_proveedor'),
        ]);

        $qb = QB::table('users us');
        $qb->join('roles ro', 'ro.id', '=', 'us.id_role');
        $qb->join('proveedores pr', 'pr.id', '=', 'us.id_proveedor');
        $qb->select(
            'us.*',
            'pr.nombre pr_nombre',
            'ro.name ro_name');

        $qb->where('us.id_type_user', User::TYPE_PROVIDER);
        $qb->where('us.state', '!=', Role::_STATE_DELETED);

        if ($fil->id_proveedor)
            $qb->where('us.id_proveedor', $fil->id_proveedor);

        if ($fil->query)
            $qb->whereLike('CONCAT(us.name,us.surname', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(us.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'ID'                => $o->id,
                'Nombre'            => $o->name,
                'Apellido'          => $o->surname,
                'Email'             => $o->email,
                'Teléfono'          => $o->phone,
                'Fecha de registro' => Util::humanDatetime($o->date_created),
            ];

            return $o;
        });
    }

    public function form(Req $req)
    {
        $rsp = Rsp::ok();

        if ($id = $req->id()) {
            if ($item = User::findOrNull($id)) {
                $rsp->setItem($item);
            } else {
                return Rsp::e404();
            }
        }

        return $rsp
            ->set('roles', Role::byTypeUser(User::TYPE_PROVIDER));
    }

    public function create(Req $req)
    {
        $data = $req->data([
            'id'           => 'id',
            'id_proveedor' => 'required|id',
            'name'         => ['required' => 'Nombre'],
            'surname'      => ['required' => 'Apellido'],
            'email'        => ['required|email' => 'Email'],
            'phone'        => 'default:',
        ]);

        if (User::existEmail($data->email, $data->id))
            return rsp('El correo electrónico ya está en uso.');

        $item = User::find($data->id);
        $item->data('id_proveedor', $data->id_proveedor);
        $item->data('id_type_user', User::TYPE_PROVIDER);
        $item->data('name', $data->name);
        $item->data('surname', $data->surname);
        $item->data('email', $data->email);
        $item->data('phone', $data->phone);

        return $item->saveRSP();
    }
}