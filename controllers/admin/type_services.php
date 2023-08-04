<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\TypeService;

class type_services extends _controller
{
    protected $no_see = ['item'];

    function index(Req $req)
    {
        $fil = $req->fil([
            'id_role' => $req->num('id_role'),
        ]);

        $qb = QB::table('type_services ts');
        $qb->select([
            'ts.*',
        ]);
        $qb->where('ts.state', '!=', TypeService::_STATE_DELETED);

        if ($fil->id)
            $qb->where('ts.id', $fil->id);

        if ($fil->query)
            $qb->whereLike('ts.name', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(ts.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'ID'     => $o->id,
                'Nombre' => $o->name,
                'Fecha'  => Util::humanDatetime($o->date_created),
            ];

            return $o;
        });
    }

    function form(Req $req)
    {
        $rsp = Rsp::ok();

        if ($id = $req->id()) {
            $item = TypeService::findOrNull($id);
            if ($item) {
                $rsp->setItem($item);
            } else {
                return Rsp::e404();
            }
        }

        return $rsp;
    }

    function create(Req $req)
    {
        $data = $req->data([
            'id'            => 'id',
            'name'          => 'required',
        ]);

        $item = TypeService::find($data->id);
        $item->data('name', $data->name);

        return $item->saveRSP();
    }

    function remove(Req $req)
    {
        return TypeService::deleteRSP($req->requiredId());
    }

}
