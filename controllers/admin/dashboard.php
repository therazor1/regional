<?php namespace Controllers\admin;

use Controllers\log_file;
use DateTime;
use Inc\Export;
use Inc\Export2;
use Inc\Fil;
use Inc\Req;
use Inc\Rsp;
use Inc\Secure;
use Inc\Util;
use Libs\Pixie\QB;
use Libs\Pixie\Raw;
use Models\Dream;
use Models\Plan;
use Models\PlanUserManager;
use Models\User;
use Models\Zona;
use stdClass;

class dashboard extends _controller
{
    #const AUTH_REQUIRED = false;

    /**
     * pintar data en DASHBOARD
     */
    public function index(Req $req)
    {
        $id_companies = $req->data([
            'id_company'   => 'num',
            'id_company.*' => 'num',
        ]);

        $fil = new Fil([
            'date_from' => $req->date('date_from', date('Y-m-01')),
            'date_to'   => $req->date('date_to', date('Y-m-d')),
        ]);

        if (is_array($id_companies->id_company)) {
            $fil->id_companies = $id_companies->id_company;
        } else {
            $fil->id_companies = ($id_companies->id_company) ? [$id_companies->id_company] : [];
        }

        $dash = new stdClass();
        $dash->getUsersPulseras = [];

        return rsp(true)
            ->set('dash', $dash);
    }

    public function graphics(Req $req)
    {
        $fil = new Fil([
            'date_from' => $req->date('date_from', date('Y-m-01')),
            'date_to'   => $req->date('date_to', date('Y-m-d')),
            'id_zona'   => $req->any('id_zona'),
        ]);

        #card 01
        $dash = new stdClass();
        $dash->getUsersPulseras = $this->getUsersPulseras($fil);
        //$dash->totalUsersPulseras = 'TOTAL: ' . $this->getTotalUsersPulseras($fil);
        $dash->totalUsersPulseras = 'TOTAL: ' . $this->getTotalUsersSyncByZone($fil);

        #card 02
        $dash->getUsersPulserasCalificacion = $this->getUsersPulserasCalificacion($fil);
        //$dash->totalUsersPulserasSync = 'TOTAL: ' . $this->getTotalUsersPulserasSync($fil);
        $dash->totalUsersPulserasSync = 'TOTAL: ' . $this->getTotalUsersSyncByZone($fil);

        return rsp(true)->set('dash', $dash);
    }


    function getTotalUsersSyncByZone(Fil $fil)
    {
        $qb = QB::table('dreams dr');
        $qb->select(
            'dr.*',
            'zo.name zo_name',
        );
        $qb->leftJoin('users us', 'us.id', '=' , 'dr.id_user');
        $qb->leftJoin('operaciones op', 'op.id_conductor', '=', 'us.id');
        $qb->leftJoin('zonas zo', 'zo.id', '=','op.id_zona');
        $qb->where('dr.state', '!=', Dream::_STATE_DELETED);
        $qb->where('dr.exists_data', Dream::SI_EXIST);
        if ($fil->id_zona)
            $qb->where('zo.id', $fil->id_zona);
        $qb->whereBetween('DATE(dr.date_created)', $fil->date_from, $fil->date_to);

        return $qb->count();
    }

    function getUsersPulseras(Fil $fil)
    {
        $chart = [];
        $totalUsersApp = $this->getTotalUsersApp($fil); #192
        $totalSync = $this->getTotalUsersSyncByZone($fil); #299
        $totalNoSync = $totalUsersApp - $totalSync;

        $chart[] = ['nombre' => 'SINCRONIZARON', 'color' => '#5595C9', 'cantidad' => $totalSync];
        $chart[] = ['nombre' => 'NO SINCRONIZARON', 'color' => '#26C281', 'cantidad' => $totalNoSync];
        return $chart;
    }

    function getUsersPulserasCalificacion(Fil $fil)
    {
        /*apto | no apto | observado*/
        $rsp = [];
        $qb = QB::table('dreams dr');
        $qb->select(
            'dr.*',
            "CONCAT(us.name,' ',us.surname) conductor",
            "us.email us_email",
            "cl.name cliente",
        );
        $qb->leftJoin('users us', 'us.id', '=', 'dr.id_user');
        $qb->leftJoin('salidas sa', 'sa.id_conductor', '=', 'us.id');
        $qb->leftJoin('clients cl', 'cl.id', '=', 'sa.id_cliente');
        $qb->leftJoin('operaciones op', 'op.id_conductor', '=', 'us.id');
        $qb->leftJoin('zonas zo', 'zo.id', '=', 'op.id_zona');
        $qb->where('us.id_role', User::ROLE_CONDUCTOR);
        $qb->where('dr.exists_data', Dream::SI_EXIST);
        if ($fil->id_zona)
            $qb->where('zo.id', $fil->id_zona);
        $qb->whereBetween('DATE(dr.date_created)', $fil->date_from, $fil->date_to);
        $qb->orderBy('dr.id', 'DESC');
        $data = $qb->get();

        foreach ($data as $o) {
            $item = [
                'calificacion' => $o->exists_data ? Dream::getPeorCalificacionGeneralTxtDinamico($o->horas_dormidas_decimal,
                    $o->profundo, $o->rem, $o->exists_data, true) : 'Sin datos',
            ];
            $rsp[] = $item;
        }

        $countApto = 0;
        $countNoApto = 0;
        $countObservado = 0;
        $countOther = 0;

        foreach ($rsp as $o) {
            if ($o['calificacion'] == Dream::STATE_APTO) {
                $countApto++;
            } else if ($o['calificacion'] == Dream::STATE_NO_APTO) {
                $countNoApto++;
            } else if ($o['calificacion'] == Dream::STATE_OBSERVADO) {
                $countObservado++;
            } else {
                $countOther++;
            }
        }

        $chart = [];
        $chart[] = ['nombre' => 'APTOS', 'color' => '#5595C9', 'cantidad' => $countApto];
        $chart[] = ['nombre' => 'NO APTOS', 'color' => '#26C281', 'cantidad' => $countNoApto];
        $chart[] = ['nombre' => 'OBSERVADOS', 'color' => '#F5AD00', 'cantidad' => $countObservado];

        return $chart;
    }

    public function getTotalUsersPulseras(Fil $fil)
    {
        //return $this->getTotalUsersApp();

        $sync = QB::table('dreams')
            ->where('state', Dream::_STATE_ENABLED)
            ->where('exists_data', Dream::SI_EXIST)
            ->whereBetween('DATE(date_created)', $fil->date_from, $fil->date_to);

        return $sync->count();
    }

    public function getTotalUsersPulserasSync(Fil $fil)
    {
        $sync = QB::table('dreams')
            ->where('state', Dream::_STATE_ENABLED)
            ->where('exists_data', Dream::SI_EXIST)
            ->whereBetween('DATE(date_created)', $fil->date_from, $fil->date_to);

        return $sync->count();
    }

    /*HELPERS*/
    public function getTotalUsersApp(Fil $fil)
    {
        $qb = QB::table('users us');
        $qb->leftJoin('dreams dr', 'dr.id_user', '=', 'us.id');
        $qb->leftJoin('operaciones op', 'op.id_conductor', '=', 'us.id');
        $qb->leftJoin('zonas zo', 'zo.id', '=', 'op.id_zona');
        $qb->where('us.id_role', User::ROLE_CONDUCTOR);
        $qb->where('us.state', User::_STATE_ENABLED);
        if ($fil->id_zona)
            $qb->where('zo.id', $fil->id_zona);
        return $qb->count();
    }


    /**
     * EXPORTS
     * La clase export acepta array de array -> [[],[],[]]
     */
    public function exportDiarioConductor(Req $req)
    {
        $fil = new Fil([
            'date_from' => $req->date('date_from', date('Y-m-1')),
            'date_to'   => $req->date('date_to', date('Y-m-t')),
            'export'    => $req->any('export'),
        ]);

        /*apto | no apto | observado*/
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
            ->where('DATE(dr.date_created)', getDateDB())
            ->orderBy('dr.id', 'DESC')
            ->get();

        foreach ($data as $o) {
            $exists_data_view = $o->exists_data ? 'SÍ' : 'Sin información';
            $item = [
                'date_created'   => Util::humanDatetime($o->date_created),
                'exists_data'    => $exists_data_view,
                'conductor'      => $o->conductor,
                'email'          => $o->us_email,
                'empresa'        => $o->empresa,
                'cliente'        => $o->cliente,
                'horas_dormidas' => $o->horas_dormidas,
                'calificacion'   => $o->exists_data ? Dream::getPeorCalificacionGeneralTxtDinamico($o->horas_dormidas_decimal,
                    $o->profundo, $o->rem, $o->exists_data, true) : 'Sin datos',
                'ligero'         => $o->ligero,
                'profundo'       => $o->exists_data ? $o->profundo . ' ' . (Dream::calificacionProfundo($o->horas_dormidas_decimal,
                        $o->profundo, $o->exists_data))->name : 'Sin datos',

                'rem'             => $o->exists_data ? $o->rem . ' ' . (Dream::calificacionREM($o->horas_dormidas_decimal,
                        $o->rem, $o->exists_data))->name : 'Sin datos',
                'horas_despierto' => $o->horas_despierto,
                'horas_dormidas'  => Dream::timeCustom($o->horas_dormidas),
            ];
            $rsp[] = $item;
        }

        $items = [];

        if ($fil->export) {
            foreach ($rsp as $o) {
                $items[] = [
                    'Fecha de sincronización' => $o['date_created'],
                    'Existe información'      => $o['exists_data'],
                    'Conductor'               => $o['conductor'],
                    'Email'                   => $o['email'],
                    'Empresa'                 => $o['empresa'],
                    'Cliente'                 => $o['cliente'],
                    'Calificacion General'    => $o['calificacion'],
                    'Ligero'                  => $o['ligero'],
                    'Profundo'                => $o['profundo'],
                    'REM'                     => $o['rem'],
                    'Horas despierto'         => $o['horas_despierto'],
                    'Total horas de sueño'    => $o['horas_dormidas'],
                ];
            }
            Export::any(Export::FORMAT_XLSX, $items, 'Reporte diario consolidado de todos los conductores');
        }

        return rsp(true);
    }

    /**
     * 2do reporte
     */
    public function exportConductorNoAptoObservado(Req $req)
    {
        $fil = new Fil([
            'date_from' => $req->date('date_from', date('Y-m-1')),
            'date_to'   => $req->date('date_to', date('Y-m-t')),
            'export'    => $req->any('export'),
        ]);

        $data = QB::query("SELECT
       dr.date_created,
       CONCAT(us.name, ' ', us.surname) conductor,
       dr.horas_dormidas_decimal,
       dr.profundo,
       dr.rem,
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

        $peorCalificacionArray = [];
        foreach ($data as $o) {
            $item = [
                'date_created'                => $o->date_created,
                'conductor'                   => $o->conductor,
                'calificacion_horas_dormidas' => Dream::getCalificacionHorasDormidasObj($o->horas_dormidas_decimal, $o->exists_data, true),
                'calificacion_profundo'       => $o->exists_data ? (Dream::calificacionProfundo($o->horas_dormidas_decimal,
                    $o->profundo, $o->exists_data)->name) : 'Sin datos',
                'calificacion_rem'            => $o->exists_data ? (Dream::calificacionREM($o->horas_dormidas_decimal,
                    $o->rem, $o->exists_data)->name) : 'Sin datos',

                'calificacion_peor' => $o->exists_data ? (Dream::getPeorCalificacionGeneralTxtDinamico($o->horas_dormidas_decimal,
                    $o->profundo, $o->rem, $o->exists_data)->name) : 'Sin datos',
                'empresa'           => $o->empresa,
                'cliente'           => $o->cliente,
            ];
            $peorCalificacionArray[] = $item;
        }

        $groupConductor = [];
        foreach ($peorCalificacionArray as $o) {
            $groupConductor[$o['conductor']][] = $o;
        }

        $rspFinal = [];
        foreach ($groupConductor as $key => $value) {
            $CONT_OBSERVADO = 0;
            $CONT_NO_APTO = 0;
            foreach ($value as $o) {
                if ($o['calificacion_peor'] == 'OBSERVADO') {
                    $CONT_OBSERVADO++;
                } else if ($o['calificacion_peor'] == 'NO APTO') {
                    $CONT_NO_APTO++;
                }
            }
            $rspFinal[$key] = [
                'detail'    => $value,
                'OBSERVADO' => $CONT_OBSERVADO,
                'NO APTO'   => $CONT_NO_APTO,
            ];
        }

        $rsp = [];
        foreach ($rspFinal as $key => $value) {
            $item = [
                'CONDUCTOR'             => $key,
                'RECURRENCIA OBSERVADO' => $value['OBSERVADO'],
                'RECURRENCIA NO APTO'   => $value['NO APTO'],
                'EMPRESA'               => !empty($value['detail']) ? $value['detail'][0]['empresa'] : '',
                'CLIENTE'               => !empty($value['detail']) ? $value['detail'][0]['cliente'] : '',
            ];

            $fechas_sync_txt = '';
            foreach ($value as $o) {
                foreach ($o as $i) {
                    $fechas_sync_txt .= $i['date_created'] . ': ' . $i['calificacion_peor'] . ' | ';
                }
                break;
            }
            $item['FECHAS DE SINCRONIZACION + CALIFICACION'] = $fechas_sync_txt;
            $rsp[] = $item;
        }

        Export::any('xlsx', $rsp, 'Reporte semanal de recurrencia de conductores en condición de no apto y observado');

        return rsp(true);
    }

    /**
     * 3er REPORTE
     */
    public function exportSemanalNoUsoPulsera(Req $req)
    {
        //CONDUCTORES SIN DATOS AL SINCRONIZAR
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
        and dr.exists_data!=1")->get();

        $rsp = [];
        foreach ($data as $o) {
            $item = [
                'Fecha de sincronización' => $o->date_created,
                'Conductor'               => $o->conductor,
                'Empresa'                 => $o->empresa,
                'Cliente'                 => $o->cliente,
                'Estado'                  => 'SIN DATOS AL SINCRONIZAR',
            ];

            $rsp[] = $item;
        }

        Export::any('xlsx', $rsp, 'Reportes semanal de no uso de pulsera');

        return rsp(true);
    }

    public function getZonas()
    {
        return Rsp::items(Zona::all('id', 'name'));
    }

}