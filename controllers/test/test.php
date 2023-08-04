<?php namespace Controllers\test;

use Inc\Export;
use Inc\Req;
use Inc\Rsp;
use Inc\STG;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Dream;
use Models\User;

class test extends _controller
{

    public function exportConductorNoAptoObservado()
    {
        $data = QB::query("SELECT
       dr.date_created,
       CONCAT(us.name, ' ', us.surname) conductor,
       dr.calificacion_horas_dormidas,
       dr.calificacion_profundo,
       dr.calificacion_rem,
       dr.calificacion_general,
       dr.exists_data,
       em.name empresa,
       cl.name cliente
from dreams dr
         join users us ON us.id = dr.id_user
         left join empresas em ON em.id = us.id_emp_transporte
         left join salidas sa ON sa.id_conductor = us.id
         left join clients cl ON cl.id = sa.id_cliente
where date(dr.date_created) >= DATE_SUB(current_date, INTERVAL 7 DAY)
  and dr.exists_data=1
  and dr.calificacion_general!='APTO'")->get();

        $peorCalificacionArray=[];
        foreach ($data as $o){
            $item=[
                'date_created'=>$o->date_created,
                'conductor'=>$o->conductor,
                'calificacion_horas_dormidas'=>$o->calificacion_horas_dormidas,
                'calificacion_profundo'=>$o->calificacion_profundo,
                'calificacion_rem'=>$o->calificacion_rem,
                'calificacion_peor'=>Dream::getPeorCalificacionGeneralTxt($o->calificacion_horas_dormidas, $o->calificacion_profundo, $o->calificacion_rem, $o->exists_data, true),
                'empresa'=>$o->empresa,
                'cliente'=>$o->cliente,
            ];

            $peorCalificacionArray[]=$item;
        }

        $groupConductor=[];
        foreach ($peorCalificacionArray as $o){
            $groupConductor[$o['conductor']][]=$o;
        }

        $rspFinal=[];
        foreach ($groupConductor as $key => $value){
            $CONT_OBSERVADO=0;
            $CONT_NO_APTO=0;
            foreach ($value as $o){
                if($o['calificacion_peor']=='OBSERVADO'){
                    $CONT_OBSERVADO++;
                }else if($o['calificacion_peor']=='NO APTO'){
                    $CONT_NO_APTO++;
                }
            }
            $rspFinal[$key]=[
                'detail'=>$value,
                'OBSERVADO'=>$CONT_OBSERVADO,
                'NO APTO'=>$CONT_NO_APTO,
            ];
        }

        $rsp=[];
        foreach ($rspFinal as $key => $value){
            $item=[
                'CONDUCTOR'=>$key,
                'RECURRENCIA OBSERVADO'=>$value['OBSERVADO'],
                'RECURRENCIA NO APTO'=>$value['NO APTO'],
                'EMPRESA'=>!empty($value['detail'])?$value['detail'][0]['empresa']:'',
                'CLIENTE'=>!empty($value['detail'])?$value['detail'][0]['cliente']:'',
            ];

            $fechas_sync_txt='';
            foreach ($value as $o){
                foreach ($o as $i){
                    $fechas_sync_txt.=$i['date_created'].': '.$i['calificacion_peor'].' | ';
                }
                break;
            }
            $item['FECHAS DE SINCRONIZACION + CALIFICACION']=$fechas_sync_txt;

            $rsp[]=$item;
        }
        Export::any('xlsx', $rsp, 'Reporte semanal de recurrencia de conductores en condición de no apto y observado');
    }


    public function id(int $id = 0)
    {
        $rsp = [];
        $qb  = QB::table('dreams dr')
            ->select('dr.*', "us.name", "us.surname")
            ->leftJoin('users us', 'us.id', '=', 'dr.id_user')
            ->where('dr.state', '!=', Dream::_STATE_DELETED)
            ->where('dr.id_user', $id)
            ->where('dr.exists_data', 1)
            ->orderBy('dr.id', 'DESC');
        /*$qb->limit(1);*/
        $data = $qb->get();

        foreach ($data as $o) {
            $item = [
                'id'                          => $o->id,
                'id_user'                     => $o->id_user,
                'inicio_dormir'               => $o->inicio_dormir,
                'fin_dormir'                  => $o->fin_dormir,
                'horas_dormidas'              => timeCustom($o->horas_dormidas),
                'horas_dormidas_decimal'      => $o->horas_dormidas_decimal,
                'ligero'                      => timeCustom($o->ligero),
                'ligero_porc'                 => formatPorcDreams($o->ligero_porc),
                'profundo'                    => timeCustom($o->profundo),
                'profundo_porc'               => formatPorcDreams($o->profundo_porc),
                'rem'                         => timeCustom($o->rem),
                'rem_porc'                    => formatPorcDreams($o->rem_porc),
                'horas_despierto'             => timeCustom($o->horas_despierto),
                'calidad'                     => $o->calidad,
                'calificacion'                => $o->calificacion,
                'sincronizado_hoy'            => $o->sincronizado_hoy,
                'exists_data'                 => $o->exists_data,
                'frecuencia_cardiaca'         => $o->frecuencia_cardiaca,
                'date_created'                => dateTextES($o->date_created),
                'calificacion_general'        => empty($o->calificacion_general) ? '' : $o->calificacion_general,
                'calificacion_horas_dormidas' => empty($o->calificacion_horas_dormidas) ? '' : $o->calificacion_horas_dormidas,
                'calificacion_ligero'         => empty($o->calificacion_ligero) ? '' : $o->calificacion_ligero,
                'calificacion_profundo'       => empty($o->calificacion_profundo) ? '' : $o->calificacion_profundo,
                'calificacion_rem'            => empty($o->calificacion_rem) ? '' : $o->calificacion_rem,
                'calificacion_peor'           => Dream::getPeorCalificacionGeneralTxt($o->calificacion_horas_dormidas,
                    $o->calificacion_profundo, $o->calificacion_rem, $o->exists_data, true),
            ];
            $rsp[]     = $item;
        }
        return $rsp; //retorna un JSONArray
    }

    public function userData(){
        //$username='stuxnetpl19@gmail.com';
        $username2='74815458';
        //$username='Demo';
        //$user = User::find('email', $username);
        //$user = User::find('document', $username2);
        $user = User::find('document', '74815854');
        if(!$user->exist()){
            echo 'false';
        }else{
            echo 'success';
        }
    }


    public function getDream(){
        $rta=Dream::getPeorCalificacionGeneralTxt('APTO', 'APTO',
            'NO APTO', 1);
        dep($rta->name);
    }


    public function id_lastweek(int $id = 0)
    {
        $rsp      = [];
        $cantDias = 7;
        $qb       = QB::query("SELECT dr.*,us.name,us.surname from dreams dr left join users us ON 
                us.id=dr.id_user WHERE dr.state !=" . Dream::_STATE_DELETED . " and dr.id_user=" . $id . " and dr.exists_data=1 and 
                date(dr.date_created) > DATE_SUB(current_date, INTERVAL " . $cantDias . " DAY) ORDER BY dr.id DESC");

        $data = $qb->get();

        foreach ($data as $o) {
            $item = [
                'id'                          => $o->id,
                'id_user'                     => $o->id_user,
                'inicio_dormir'               => $o->inicio_dormir,
                'fin_dormir'                  => $o->fin_dormir,
                'horas_dormidas'              => timeCustom($o->horas_dormidas),
                'horas_dormidas_decimal'      => $o->horas_dormidas_decimal,
                'ligero'                      => timeCustom($o->ligero),
                'ligero_porc'                 => formatPorcDreams($o->ligero_porc),
                'profundo'                    => timeCustom($o->profundo),
                'profundo_porc'               => formatPorcDreams($o->profundo_porc),
                'rem'                         => timeCustom($o->rem),
                'rem_porc'                    => formatPorcDreams($o->rem_porc),
                'horas_despierto'             => timeCustom($o->horas_despierto),
                'calidad'                     => $o->calidad,
                'calificacion'                => $o->calificacion,
                'sincronizado_hoy'            => $o->sincronizado_hoy,
                'exists_data'                 => $o->exists_data,
                'frecuencia_cardiaca'         => $o->frecuencia_cardiaca,
                'date_created'                => dateTextES($o->date_created),
                'calificacion_general'        => empty($o->calificacion_general) ? '' : $o->calificacion_general,
                //'calificacion_general'        => 'test',
                'calificacion_horas_dormidas' => Dream::getCalificacionHorasDormidasObj($o->horas_dormidas_decimal, $o->exists_data, true),
                'calificacion_ligero'         => empty($o->calificacion_ligero) ? '' : $o->calificacion_ligero,
                'calificacion_profundo'       => Dream::calificacionProfundo($o->horas_dormidas_decimal, $o->profundo,$o->exists_data)->name,
                'calificacion_rem'            => Dream::calificacionREM($o->horas_dormidas_decimal, $o->rem, $o->exists_data)->name,
                'calificacion_peor'           => Dream::getPeorCalificacionGeneralTxtDinamico($o->horas_dormidas_decimal,
                    $o->profundo, $o->rem, $o->exists_data, true),
            ];
            $rsp[]     = $item;
        }

        /**
         *Antes: solo retornamos $data
         */
        return $rsp; //retorna un JSONArray
    }

    public static function getColaboradoresApp()
    {
        $qb = QB::table('users')
            ->where('id_role', User::ROLE_CONDUCTOR)
            ->where('state', User::_STATE_ENABLED)
            ->get();

        return $qb;


         /*return Rsp::ok()
             ->set('users', $qb->get())
             ->set('count', $qb->count());*/
    }

    public static function getCalificacion(){

        $rsp = [];
        $data = QB::table('dreams dr')->select(
            'dr.*',
            "CONCAT(us.name,' ',us.surname) conductor",
            "us.email us_email",
            "em.name empresa",
            "cl.name cliente",
        )
            ->leftJoin('users us', 'us.id', '=', 'dr.id_user')
            ->leftJoin('empresas em', 'em.id', '=', 'us.id_emp_transporte')
            ->leftJoin('salidas sa', 'sa.id_conductor', '=', 'us.id')
            ->leftJoin('clients cl', 'cl.id', '=', 'sa.id_cliente')
            ->where('us.id_role', User::ROLE_CONDUCTOR)
            ->orderBy('dr.id', 'DESC')
            ->get();

        foreach ($data as $o){
            $item=[
                'calificacion'=>$o->exists_data ? Dream::getPeorCalificacionGeneralTxtDinamico($o->horas_dormidas_decimal,
                    $o->profundo, $o->rem, $o->exists_data, true) : 'Sin datos',
            ];
            $rsp[] = $item;
        }

        $countApto=0;
        $countNoApto=0;
        $countObservado=0;
        $countOther=0;

        //return $rsp;

        foreach ($rsp as $o){
            if($o['calificacion']==Dream::STATE_APTO){
                $countApto++;
            }else if($o['calificacion']==Dream::STATE_NO_APTO){
                $countNoApto++;
            }else if($o['calificacion']==Dream::STATE_OBSERVADO){
                $countObservado++;
            }else{
                $countOther++;
            }
        }

        $contArr=[
            'countApto'=>$countApto,
            'countNoApto'=>$countNoApto,
            'countObservado'=>$countObservado,
            'countOther'=>$countOther,
        ];

        return $contArr;

    }

}
