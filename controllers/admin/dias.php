<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Client;
use Models\Operacion;
use Models\Zona;
use Models\User;
use Models\Empresa;
use Models\Dia;

class dias extends _controller
{
    function index(Req $req)
    {
        $fil = $req->fil();

        $qb = QB::table('operaciones op');
        //id_role 4 = supervidor
        //id_role 5 = conductor
        $qb->select(
            'op.*',
            'us.name conductor',
            "em.name empresa",
            'super.name supervisor',
            'super.email email_su',
            'cl.name cliente',
            'zo.name zona'
        );
        $qb->leftJoin('users us', 'us.id','=','op.id_conductor');
        $qb->leftJoin('empresas em', 'em.id','=','op.id_emp_transporte');
        $qb->leftJoin('users super', 'super.id','=','op.id_supervisor');

        $qb->leftJoin('clients cl', 'cl.id','=','op.id_cliente');
        $qb->leftJoin('zonas zo', 'zo.id','=','op.id_zona');
        $qb->where('op.state', '!=', Operacion::_STATE_DELETED);

        //$qb->showSQL();

        if ($fil->id)
            $qb->where('op.id', $fil->id);

        if ($fil->query)
            $qb->whereLike('CONCAT(us.name,em.name,super.name,super.email)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(op.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'Nombre del conductor'               => $o->conductor,
                'Empresa de transporte'           => $o->empresa,
                'Supervisor'              => $o->supervisor,
                'Correo del supervidor' => $o->email_su,
                'Negocio/Zona' => $o->zona,
                'Fecha'            => Util::humanDatetime($o->date_created),
            ];

            return $o;
        });
    }

    function form(Req $req)
    {
        $rsp = Rsp::ok(); //dual funcionalidad

        if ($id = $req->id()) {//captura el id
            if ($item = Dia::findOrNull($id)) {
               // $rsp->set('item', $item);
                $rsp->setItem($item);//aquí enviamos el "id" para editar y eliminar
                //aquí se hace la logica
            } else {
                return Rsp::e404();
            }
        }

        //======output======
        $rsp->set('zonas', Zona::all());
        $rsp->set('supervisores', User::where('id_role',User::ROLE_SUPERVISOR)->select([
            'id',
            "concat(name,' ',surname) " =>'name',
            "email"
        ])->get());

        $rsp->set('conductores', User::where('id_role',User::ROLE_CONDUCTOR)->select('id',"concat(name,' ',surname) name")->get());
        $rsp->set('transporte', Empresa::where('id_type_empresa',Empresa::TYPE_EMPRESA_TRANSPORTE)->select('id','name')->get());
        $rsp->set('clientes', Client::all());

        return $rsp;
    }

    function create(Req $req)
    {
        $data = $req->data([
            'id'       => 'id',
            'id_conductor'     => ['required' => 'conductor'],
            //'id_emp_transporte' => ['required' => 'empresa de transporte'],
            'id_supervisor'  => ['required' => 'supervidor'],
            'email_supervisor'  => 'default:',
            'id_cliente'  => ['required' => 'cliente'],
            'id_zona'  => ['required' => 'zona']
        ]);

        $item = Operacion::find($data->id);
        $item->datas($data);

        return $item->saveRSP();
    }

    function remove(Req $req)
    {
        //return $req->requiredId();
        return Operacion::deleteRSP($req->requiredId());
        //return Operacion::deleteRSP($req->num('id'));

    }

}
