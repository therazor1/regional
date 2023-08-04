<?php namespace Controllers\admin;

use Inc\Auth;
use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Module;
use Models\Role;
use Models\TypeUser;

class roles extends _controller
{

    public function index(Req $req)
    {
        $fil = $req->fil([
            'id_type_user' => $req->num('id_type_user'),
        ]);

        $qb = QB::table('roles ro');
        $qb->select([
            'ro.*',

            'tu.name' => 'tu_name',
            'mo.name' => 'mo_name',

            'COUNT(us.id) num_users',
        ]);
//        $qb->join('type_users tu', 'tu.id', '=', 'ro.id_type_user');
//        $qb->join('modules mo', 'mo.id', '=', 'ro.id_module');

        $qb->leftJoin('type_users tu', 'tu.id', '=', 'ro.id_type_user');
        $qb->leftJoin('modules mo', 'mo.id', '=', 'ro.id_module');

        $qb->leftJoin('users us', 'us.id_role', '=', 'ro.id');
        $qb->where('ro.state', '!=', Role::_STATE_DELETED);

        if ($fil->id)
            $qb->where('ro.id', $fil->id);

        if ($fil->id_type_user)
            $qb->where('ro.id_type_user', $fil->id_type_user);

        if ($fil->query)
            $qb->where('ro.name', 'LIKE', '%' . str_replace(' ', '%', $fil->query) . '%');

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(ro.date_created)', $fil->date_from, $fil->date_to);

        $qb->groupBy('ro.id');

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'ID'     => $o->id,
                'Nombre' => $o->name,
                'Fecha'  => Util::humanDatetime($o->date_created),
            ];

            return $o;
        });
    }

    public function create(Req $req)
    {
        $data = $req->data([
            'id_type_user'   => 'required|id',
            'name'           => 'required',
            'menu_collapsed' => 'bool',
            'aprobador'      => 'bool',
            'id_module'      => 'id',
        ]);

        if ($data->id_module <= 0) {
            return rsp('Selecciona el módulo de inicio.');
        }

        $item = Role::find($req->id());
        $item->data('id_type_user', $data->id_type_user);
        $item->data('id_module', $data->id_module);
        $item->data('menu_collapsed', $data->menu_collapsed);
        $item->data('name', $data->name);

        if ($item->id != Role::ID_ROOT && ($item->name == 'Root' || $item->name == 'root')) {
            return rsp('Nombre reservado');
        }

        $r = $item->saveRSP();

        if ($r->ok) {

            QB::table('perms')->where('id_role', $item->id)->delete();

            foreach ($req->arrJSON('perms') as $o) {
                QB::table('perms')->insert([
                    'id_role'   => $item->id,
                    'id_module' => $o->id_module,
                    'see'       => $o->see,
                    'edit'      => $o->edit,
                ]);
            }

        }

        return $r;
    }

    /*public function form(Req $req)
    {
        $rsp = Rsp::ok();

        $item = null;

        if ($id = $req->id()) {
            $item = QB::table('roles')->where('id', $id)->first();
            if ($item) {
                $item->protected = ($item->id == Role::ID_ROOT);
                $modules = $this->_roles_modules_by_id_type_user($item->id_type_user, $item->id);
                $rsp->set('modules', $modules);
            } else {
                return Rsp::e404();
            }
        }

        $rsp->set('item', $item);
        $rsp->set('type_users', TypeUser::all());

        return $rsp;
    }*/

    public function form(Req $req)
    {

        //$id = $req->id();
        /**
         * $id = $req->id();
         * cuando es nuevo = 0 | cuando es edit registro = idFila
         */

        $rsp = Rsp::ok();
        $item = null;

        if ($id = $req->id()) {
            $item = QB::table('roles')->where('id', $id)->first();
            if ($item) {
                $item->protected = ($item->id == Role::ID_ROOT);
              /*  $modules = $this->_roles_modules_by_id_type_user($item->id);
                $rsp->set('modules', $modules);*/
            } else {
                return Rsp::e404();
            }
        }

        $rsp->set('item', $item);
        $rsp->set('type_users', TypeUser::all());
        $rsp->set('modules', $this->modules($id));

        return $rsp;
    }

    /*public function modules(Req $req)
    {
        $data = $req->data([
            'id_type_user' => 'required|id',
            'id_role'      => 'id',
        ]);

        $modules = $this->_roles_modules_by_id_type_user($data->id_type_user, $data->id_role);

        return Rsp::items($modules);
    }

    private function _roles_modules_by_id_type_user($id_type_user, $id_role = '')
    {
        $qb = QB::table('modules mo');
        $qb->select(
            'mo.*',
            'pe.see',
            'pe.edit');
        $qb->leftJoin('perms pe', function ($t) use ($id_role) {
            $t->on('pe.id_module', '=', 'mo.id');
            $t->on('pe.id_role', '=', $id_role);
        });
        if (!Auth::root())
            $qb->where('mo.root', '0');
        $qb->where('mo.id_type_user', $id_type_user);
        $qb->where('mo.state', Module::_STATE_ENABLED);
        $qb->orderBy('mo.sort');
        $qb->groupBy('mo.id');

        $modules = $qb->get();

        foreach ($modules as $o) {
            $o->see = ($o->see == '1');
            $o->edit = ($o->edit == '1');
        }

        return $modules;
    }*/

    private function modules($id_role = '')
    {
        $qb = QB::table('modules mo');
        $qb->select(
            'mo.*',
            'pe.see',
            'pe.edit');
        $qb->leftJoin('perms pe', function ($t) use ($id_role) {
            $t->on('pe.id_module', '=', 'mo.id');
            $t->on('pe.id_role', '=', $id_role);
        });
        if (!Auth::root())
            $qb->where('mo.root', '0');
        $qb->where('mo.state', Module::_STATE_ENABLED);
        $qb->orderBy('mo.sort');
        $qb->groupBy('mo.id');

        $modules = $qb->get();

        foreach ($modules as $o) {
            $o->see = ($o->see == '1');
            $o->edit = ($o->edit == '1');
        }

        return $modules;
    }


    public function enable(Req $req)
    {
        return Role::enableRSP($req->requiredId());
    }

    public function disable(Req $req)
    {
        return Role::disableRSP($req->requiredId());
    }

    public function remove(Req $req)
    {
        return Role::deleteRSP($req->requiredId());
    }

}
