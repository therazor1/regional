<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Client;
use Models\Reniec;
use Models\User;

class clients extends _controller
{

    function index(Req $req)
    {
        $fil = $req->fil();

        $qb = QB::table('clients cl');
        $qb->select([
            'cl.*',
        ]);
        $qb->where('cl.state', '!=', Client::_STATE_DELETED);

        if ($fil->id)
            $qb->where('cl.id', $fil->id);

        if ($fil->query)
            $qb->whereLike('CONCAT(cl.name,cl.document,cl.address)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(cl.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'ID'               => $o->id,
                'Nombre'           => $o->name,
                'RUC'              => $o->document,
                'Dirección fiscal' => $o->address,
                'Fecha'            => Util::humanDatetime($o->date_created),
            ];

            unset($o->password);
            $o->escolta_obj   = User::escoltaObj($o->escolta);

            return $o;
        });
    }

    function form(Req $req)
    {

        $item = Client::find($req->num('id'));

       /* $rsp = Rsp::ok();
        if ($id = $req->id()) {
            $item = Client::findOrNull($id);
            if ($item) {
                $rsp->setItem($item);
            } else {
                return Rsp::e404();
            }
        }
        return $rsp;*/


        return rsp(true)
            ->set('item', $item) //enviamos id para editar
            ->set('escoltas', [
                pair(1, 'SI'),
                pair(0, 'NO'),
            ]);
    }

    function create(Req $req)
    {
        $data = $req->data([
            'id'       => 'id',
            'name'     => ['required' => 'Razón social'],
            'escolta' => 'required'
        ]);

        if (Client::existClient($data->name)) {
            return rsp('El cliente ya está registrado.');
        }

        $item = Client::find($data->id);
        $item->data('name', $data->name);
        $item->data('escolta', $data->escolta);
      /*  $item->data('document', $data->document);
        $item->data('address', $data->address);*/

        return $item->saveRSP();
    }

    function remove(Req $req)
    {
        return Client::deleteRSP($req->requiredId());
    }

    function searchReniec(Req $req)
    {
        $item = new Reniec($req->any('document'));
        return $item->datos();
    }
}
