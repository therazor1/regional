<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Reniec;
use Models\Empresa;
use Models\Zona;


class zonas extends _controller
{

    function index(Req $req)
    {
        $fil = $req->fil();

        $qb = QB::table('zonas zo');
        $qb->select([
            'zo.*'
        ]);
        $qb->where('zo.state', '!=', Zona::_STATE_DELETED);

        if ($fil->id)
            $qb->where('zo.id', $fil->id);

        //buscador ->obligatorio
        if ($fil->query)
            $qb->whereLike('CONCAT(zo.name)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(zo.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'Zona'            => ($o->name),
                'Fecha de registro' => Util::humanDatetime($o->date_created),
            ];
            return $o;
        });
    }

    function form(Req $req)
    {

        $rsp = Rsp::ok();

        if ($id = $req->id()) {
            //$item = Client::findOrNull($id);
            $item = Zona::findOrNull($id);
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
            'id'       => 'id',
            'name'     => ['required' => 'Nombre de Zona'], //PUEDES ESPECIFICAR EL TEXTO DEL ERROR
        ]);

        $item = Zona::find($data->id);
        $item->data('name', $data->name);
        return $item->saveRSP();
    }

    function remove(Req $req)
    {
        return Zona::deleteRSP($req->requiredId());
    }
}
