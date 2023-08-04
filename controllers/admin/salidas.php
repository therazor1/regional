<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Client;
use Models\Salida;
use Models\User;

class salidas extends _controller
{
    function index(Req $req)
    {
        $fil = $req->fil();

        $qb = QB::table('salidas sa');
        $qb->select(
            'sa.*',
            "CONCAT(us.name,' ',us.surname) conductor",
            "cl.name cliente",
            'sa.escolta escolta'
        );
        $qb->leftJoin('users us', 'us.id','=','sa.id_conductor');
        $qb->leftJoin('clients cl', 'cl.id','=','sa.id_cliente');
        $qb->where('sa.state', '!=', Salida::_STATE_DELETED);


        if ($fil->id)
            $qb->where('sa.id', $fil->id);

        if ($fil->query)
            $qb->whereLike('CONCAT(us.name,us.surname,cl.name)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(sa.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'Nombre del conductor'               => $o->conductor,
                'Cliente asignado'           => $o->cliente,
                'Horario inicio'              => $o->hora_inicio,
                'Escolta' => $o->escolta,
                'Fecha'            => Util::humanDatetime($o->date_created),
            ];
            $o->escolta_obj   = User::escoltaObj($o->escolta);

            return $o;
        });
    }

    function form(Req $req)
    {
        $rsp = Rsp::ok(); //siempre utilizar esto en el form -> permite enviar datos de DB para cargar en select, etc
        $rsp->setItem(Salida::findOrNull($req->id()));//aquí enviamos el "id" para editar y eliminar

        //======output======
        $rsp->set('conductores', User::where('id_role',User::ROLE_CONDUCTOR)->select('id',"concat(name,' ',surname) name")->get());
        $rsp->set('clientes', Client::all());
        $rsp->set('escoltas', [
            pair(1, 'SI'),
            pair(0, 'NO'),
        ]);

        return $rsp;
    }

    function create(Req $req)
    {
        $data = $req->data([
            'id'       => 'id',
            'id_conductor'     => ['required' => 'conductor'],
            'id_cliente'  => ['required' => 'cliente'],
            'hora_inicio'  => ['required' => 'horario'],
            'escolta'  => ['required' => 'Escolta']
        ]);

        $item = Salida::find($data->id);
        $item->data('id_conductor', $data->id_conductor);
        $item->data('id_cliente', $data->id_cliente);
        $item->data('hora_inicio', $data->hora_inicio);
        $item->data('escolta', $data->escolta);


        return $item->saveRSP();
    }

    function remove(Req $req)
    {
        return Salida::deleteRSP($req->requiredId());
        //return Operacion::deleteRSP($req->num('id'));
    }

}
