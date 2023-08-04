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

class operaciones extends _controller
{
    function index(Req $req)
    {
        $fil = $req->fil();
        $qb = QB::table('operaciones op');
        //id_role 4 = supervidor
        //id_role 5 = conductor
        $qb->select(
            'op.*',
            "CONCAT(us.name,' ',us.surname) conductor",
            "em.name empresa",
            "CONCAT(super.name,' ',super.surname) supervisor",
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

    function conductores(Req $req){
        $rsp = Rsp::ok();
        //$rsp->set('conductores', User::where('id_role',User::ROLE_CONDUCTOR)->select('id',"concat(name,' ',surname) name")->get());

        $conductores = QB::table('users') //todos los conductores
            ->select(
                'id',
                "CONCAT(name,' ',surname)"
             )
            ->where('id_role', User::ROLE_CONDUCTOR)
            ->where('id_emp_transporte', $rsp->id_emp_transporte)
            ->get();
        $rsp->set('conductores', $conductores);
    }

    function form(Req $req)
    {
        $rsp = Rsp::ok(); //siempre utilizar esto en el form -> permite enviar datos de DB para cargar en select, etc

        if ($id = $req->id()) {//captura el id
            if ($item = Operacion::findOrNull($id)) {
               // $rsp->set('item', $item);
                $rsp->setItem($item);//aquí enviamos el "id" para editar y eliminar
                //aquí se hace la logica
                $transporte = Empresa::findOrNull($item->id_emp_transporte);
                $rsp->set('transporte', [
                    'value' => $transporte->id,
                    'label' => $transporte->name,
                ]);

                $conductor=User::findOrNull($item->id_conductor);
                $rsp->set('conductor', [
                    'value' => $conductor->id,
                    'label' => $conductor->name.' '.$conductor->surname,
                ]);

            } else {
                return Rsp::e404();
            }
        }
        //Aquí enviamos tablas sin logica o condiciones
        //======output======
        $rsp->set('zonas', Zona::all());
        $rsp->set('supervisores', User::where('id_role',User::ROLE_SUPERVISOR)->select([
            'id',
            "concat(name,' ',surname) " =>'name',
            "email"
        ])->get());

        //$rsp->set('transportes', Empresa::where('id_type_empresa',Empresa::TYPE_EMPRESA_TRANSPORTE)->select('id','name')->get());
        $trans=QB::table('empresas')->select('id, name')->where('id_type_empresa',Empresa::TYPE_EMPRESA_TRANSPORTE)->get();
        $rsp->set('transportes', $trans);

        $rsp->set('conductores', User::where('id_role',User::ROLE_CONDUCTOR)->select('id', 'id_emp_transporte',"concat(name,' ',surname) name")->get());
        $rsp->set('clientes', Client::all());

        return $rsp;
    }

    function create(Req $req)
    {
        $data = $req->data([
            'id'       => 'id',
            'id_emp_transporte'     => ['required' => 'Empresa de transporte'],
            'id_conductor'     => ['required' => 'conductor'],
            'id_supervisor'  => ['required' => 'supervidor'],
            'email_supervisor'  => 'default:',
            'id_cliente'  => ['required' => 'cliente'],
            'id_zona'  => ['required' => 'zona']
        ]);

        $item = Operacion::find($data->id);
        $item->datas($data);
//        $item->data('id_conductor', $data->id_conductor);
//        $item->data('id_emp_transporte', $data->id_emp_transporte);
//        $item->data('id_supervisor', $data->id_supervisor);
//        $item->data('email_supervisor', $data->email_supervisor);
//        $item->data('id_cliente', $data->id_cliente);
//        $item->data('id_zona', $data->id_zona);

        return $item->saveRSP();
    }

    function remove(Req $req)
    {
        //return $req->requiredId();
        return Operacion::deleteRSP($req->requiredId());
        //return Operacion::deleteRSP($req->num('id'));

    }

}
