<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Client;
use Models\Frecuencia;
use Models\Salida;
use Models\User;

class frecuencias extends _controller
{
    function index(Req $req)
    {
        //filtros
        $fil = $req->fil();

        $qb = QB::table('frecuencias fr');
        $qb->select(
            'fr.*',
            "cl.name cliente"
        );
        $qb->leftJoin('clients cl', 'cl.id','=','fr.id_cliente');
        $qb->where('fr.state', '!=', Frecuencia::_STATE_DELETED);

        //$qb->showSQL();

        if ($fil->id)
            $qb->where('fr.id', $fil->id);

        if ($fil->query)
            $qb->whereLike('CONCAT(cl.name)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(fr.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'Cliente asignado'           => $o->cliente,
                //'Escolta' => $o->escolta,
                'Frecuencia' => $o->frecuencia,
                'Veces' => $o->veces,
                'Fecha'            => Util::humanDatetime($o->date_created),
            ];
            $o->escolta_obj   = User::escoltaObj($o->escolta);

            return $o;
        });
    }

    function form(Req $req)
    {
        $rsp = Rsp::ok(); //siempre utilizar esto en el form -> permite enviar datos de DB para cargar en select, etc

        if ($id = $req->id()) {//captura el id
            if ($item = Frecuencia::findOrNull($id)) {
               // $rsp->set('item', $item);
                $rsp->setItem($item);//aquí enviamos el "id" para editar y eliminar
            } else {
                return Rsp::e404();
            }
        }
        //======output======
        $rsp->set('clientes', Client::all());

        $rsp->set('frecuencias', [
            pair('DIARIA', 'DIARIA'),
            pair('SEMANA', 'SEMANA'),
            pair('MENSUAL', 'MENSUAL')
        ]);

        /*$rsp->set('escoltas', [
            pair(1, 'SI'),
            pair(0, 'NO'),
        ]);*/

        return $rsp;
    }

    function create(Req $req)
    {
        $data = $req->data([
            'id'       => 'id',
            'id_cliente'  => ['required' => 'cliente'],
            //'escolta'  => ['required' => 'Escolta'],
            'frecuencia'  => ['required' => 'frecuencia'],
            'veces'  => 'required'
        ]);

        $item = Frecuencia::find($data->id);
        $item->data('id_cliente', $data->id_cliente);
        //$item->data('escolta', $data->escolta);
        $item->data('frecuencia', $data->frecuencia);
        $item->data('veces', $data->veces);
        /**
         *saveRSP ->update or insert DB
         */
        return $item->saveRSP();
    }

    function remove(Req $req)
    {
        return Frecuencia::deleteRSP($req->requiredId());
        //return Operacion::deleteRSP($req->num('id'));
    }

}
