<?php namespace Controllers\admin;

use Inc\Auth;
use Inc\Fil;
use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;

//use Models\Empresa;
use Models\Consumable;
use Models\Dream;

class dreams extends _controller
{
    public function index(Req $req)
    {
        $fil = $req->fil([
            'em_id'         => $req->num('em_id'),
        ]);

        $qb = QB::table('dreams dr');
        $qb->select(
            'dr.*',
            'us.name',
            'us.surname',
            'us.id id_user',
            'em.name em_name',
            'em.id em_id',
            'zo.name zo_name',
        );
        $qb->leftJoin('users us', 'us.id', '=', 'dr.id_user');
        $qb->leftJoin('empresas em', 'em.id', '=', 'us.id_emp_transporte');
        $qb->leftJoin('operaciones op', 'op.id_conductor', '=', 'us.id');
        $qb->leftJoin('zonas zo', 'zo.id', '=', 'op.id_zona');
        $qb->where('dr.state', '!=', Dream::_STATE_DELETED);
        $qb->where('dr.exists_data', Dream::SI_EXIST);
        //dep($qb->get()); aaasss

        if ($fil->query)
            $qb->whereLike('CONCAT(us.name,us.surname)', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(dr.date_created)', $fil->date_from, $fil->date_to);

        if ($fil->em_id)
            $qb->where('em.id', $fil->em_id);

        //Forma 2
        //$qb->asObject(Dream::class);

        return $fil->pager($qb, function ($o) use ($fil) {
            $o->estado_obj = Dream::stateObj($o->state);

            //TODO: Calificacion Only horas dormidas -> ETIQUETA + COLOR
            $o->calificacion_horas_dormidas_obj = Dream::getCalificacionHorasDormidasObj($o->horas_dormidas_decimal, $o->exists_data);
            $o->horas_dormidas=Dream::timeCustom($o->horas_dormidas);

            //TODO: Label "CALIFICACIÓN PEOR"
            $o->calificacion_peor_obj = Dream::getPeorCalificacionGeneralTxtDinamico($o->horas_dormidas_decimal,
                $o->profundo, $o->rem, $o->exists_data);

            $o->ligero=Dream::timeCustom($o->ligero).' | '.formatPorcDreams($o->ligero_porc);

            //TODO 1. ETIQUETA PROFUNDO
            $bk_profundo=$o->profundo;
            $o->profundo=Dream::timeCustom($o->profundo).' | '.formatPorcDreams($o->profundo_porc); // 1 h 33 min | 20%
            $o->profundo_obj=Dream::calificacionProfundo($o->horas_dormidas_decimal, $bk_profundo, $o->exists_data); //APTO


            //TODO: ETIQUETA REM
            $bk_rem=$o->rem;
            $o->rem=Dream::timeCustom($o->rem).' | '.formatPorcDreams($o->rem_porc);
            $o->rem_obj=Dream::calificacionREM($o->horas_dormidas_decimal, $bk_rem,$o->exists_data);

            $o->horas_despierto=Dream::timeCustom($o->horas_despierto);

            if ($fil->export) return [
                //No se puede poner $o->us.name
                'ID'                      => $o->id,
                'Colaborador'             => $o->name.' '.$o->surname,
                'Empresa transporte'      => $o->em_name,
                'Zona'                    => $o->zo_name,
                'Inicio dormir'           => $o->inicio_dormir,
                'Fin dormir'              => $o->fin_dormir,
                'Horas dormidas (HD)'          => $o->horas_dormidas,
                'Calificación HD'          => $o->calificacion_horas_dormidas_obj->name,
                'Calidad'                 => $o->calidad,
                'Ligero'                  => $o->ligero,
                'Profundo'                => $o->profundo,
                'Calificación Pr'                => $o->profundo_obj->name,
                'REM'                     => $o->rem,
                'Calificación REM'                     => $o->rem_obj->name,
                'Horas despiero'          => $o->horas_despierto,
                'Fecha de sincronización' => Util::humanDatetime($o->date_created),
                'Calificación total' => $o->calificacion_peor_obj->name,
            ];

            return $o;
        });
    }

    public function create(Req $req)
    {
        $data = $req->data([
            'id'            => 'id',
            'inicio_dormir' => 'datetime',
            'calidad'       => 'required|num|min:0|max:100',
        ]);

        $item = Dream::find($data->id);
        $item->datas($data);

        return $item->saveRSP();
    }

    public function form(Req $req)
    {
        $rsp = Rsp::ok();

        if ($id = $req->id()) {
            $item = Dream::findOrNull($id);
            if ($item) {
                $rsp->setItem($item);
            } else {
                return Rsp::e404();
            }
        }

        return $rsp;
    }

    public function remove(Req $req)
    {
        return Dream::deleteRSP($req->requiredId());
    }

    public function enable(Req $req)
    {
        return Dream::enableRSP($req->requiredId());
    }

    public function disable(Req $req)
    {
        return Dream::disableRSP($req->requiredId());
    }

    public function exportPDF(Req $req)
    {

        $qb = QB::table('dreams dr');
        $qb->select('dr.*', "us.name", "us.surname", "us.id id_user");
        $qb->leftJoin('users us', 'us.id', '=', 'dr.id_user');
        $qb->where('dr.state', '!=', Dream::_STATE_DELETED);
        $qb->where('dr.exists_data', 1);
        return $qb->get();

        /*$fil = new Fil([
            'date_from' => $req->date('date_from', date('Y-m-1')),
            'date_to' => $req->date('date_to', date('Y-m-t')),
            'export' => $req->any('export'),
        ]);*/
    }
}