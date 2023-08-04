<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Module;

class modules extends _controller
{

    public function index(Req $req)
    {
        $fil = $req->fil([
            'id_type_user' => $req->num('id_type_user'),
        ]);

        $qb = QB::table('modules mo');
        $qb->where('mo.state', '!=', Module::_STATE_DELETED);

        if ($fil->id_type_user)
            $qb->where('mo.id_type_user', $fil->id_type_user);

        $qb->orderBy('mo.sort');

        $items = Util::ordMenu($qb->get());

        return Rsp::items($items);
    }

    public function create(Req $req)
    {
        $data = $req->data([
            'name'    => 'required',
            'icon'    => '',
            'section' => 'bool',
        ]);

        $item = Module::find($req->id());
        $item->datas($data);
        if (!$item->exist() || $item->root == 0) {
            $item->data('url', $req->any('url'));
        }

        return $item->saveRSP();
    }

    public function remove(Req $req)
    {
        $item = Module::find($req->id());
        if ($item->root == 1) {
            return rsp('No es posible eliminarlo');
        } else {
            return Module::deleteRSP($req->id(), true);
        }
    }

    public function enable(Req $req)
    {
        return Module::enableRSP($req->id());
    }

    public function disable(Req $req)
    {
        return Module::disableRSP($req->id());
    }

    public function form(Req $req)
    {
        return Rsp::item(Module::find($req->id()));
    }

    public function resort(Req $req)
    {
        $this->_save_list_resort($req->arr('items'));
        return rsp(true);
    }

    private function _save_list_resort($list, $id_parent = 0, &$sort = 0, $level = 0)
    {
        foreach ($list as $item) {
            $sort++;

            Module::where($item['id'])->update([
                'id_parent' => $id_parent,
                'sort'      => $sort,
                'level'     => $level
            ]);

            if (isset($item['children'])) {
                $this->_save_list_resort($item['children'], $item['id'], $sort, $level + 1);
            }
        }
    }

}