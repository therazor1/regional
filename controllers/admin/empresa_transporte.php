<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Reniec;
use Models\Empresa;


class empresa_transporte extends _controller
{

    function index(Req $req)
    {
        $fil = $req->fil();

        $qb = QB::table('empresas em');
        $qb->select([
            'em.*,te.name transporte',
        ]);
        $qb->leftJoin('type_empresa te', 'te.id', '=', 'em.id_type_empresa');
        $qb->where('em.state', '!=', Empresa::_STATE_DELETED);
        $qb->where('em.id_type_empresa', '=', Empresa::TYPE_EMPRESA_TRANSPORTE);

        if ($fil->id)
            $qb->where('em.id', $fil->id);

        //buscador ->obligatorio
        if ($fil->query)
            $qb->whereLike('CONCAT(em.name)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(em.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'Nombre empresa'            => ($o->name),
                'Fecha de registro' => Util::humanDatetime($o->date_created),
            ];
            //Aquí antes de enviar -> puedes hacer condicionales
//            $o->escolta_obj   = User::escoltaObj($o->escolta);
//            $o->state_laboral = User::state_laboraObj($o->state_laboral);

            return $o;
        });
    }

    function form(Req $req)
    {

        $rsp = Rsp::ok();

        if ($id = $req->id()) {
            //$item = Client::findOrNull($id);
            $item = Empresa::findOrNull($id);
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
            'name'     => ['required' => 'Nombre de empresa'], //PUEDES ESPECIFICAR EL TEXTO DEL ERROR
        ]);

        if (Empresa::existEmpresa($data->name)) {
            return rsp('La empresa ya está registrada.');
        }

        $item = Empresa::find($data->id);
        $item->data('name', $data->name);
        //$item->data('id_type_empresa', Empresa::TYPE_EMPRESA_TRANSPORTE);
        $item->data('id_type_empresa', 1);
        return $item->saveRSP();
    }

    function remove(Req $req)
    {
        return Empresa::deleteRSP($req->requiredId());
    }

}
