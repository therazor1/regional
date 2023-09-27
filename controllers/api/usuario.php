<?php namespace Controllers\api;

use Models\Dias;
use Libs\Pixie\QB;
use Inc\Req;
use Inc\Rsp;

class usuario extends _controller{


    public function __construct(){
        parent::__construct(false);
    }


    public static function getMessage(Req $req){

        $data = $req->data([
            'id_user' => 'required|num'
        ]);
        $hora = date("H");
        // $hora = '14';
        $dia = date('l');
        $dia = Dias::getDay($dia);
        // // ID Acciones Completadas
        // $acciones = QB::table('registro')->select(['accion_diaria'])->where('id_user', $data->id_user)->where('fecha', getToday())->get()[0]->accion_diaria;
        // $acciones = json_decode($acciones, true);
        $acciones = QB::query("SELECT re.id, ad.status, ad.id_mensaje FROM registro re
            LEFT JOIN accion_diaria ad ON ad.id_registro = re.id
            WHERE re.id_user = $data->id_user
            AND ad.fecha = '".getToday()."'
            AND TIME_FORMAT(ad.hora, '%H:%i:%s') BETWEEN '$hora:00:00' AND '$hora:59:00'
        ")->get()[0];

        $qb = QB::query("SELECT acciones_semanales.hora, mensajes.mensaje_accion, mensajes.productos_seleccionados, mensajes.retroalimentacion, mensajes.id as id_mensaje, es.nombre as estado_mensaje FROM acciones_semanales
            LEFT JOIN mensajes ON mensajes.id = acciones_semanales.id_mensaje
            LEFT JOIN estados es ON es.id = mensajes.id_estados
            WHERE acciones_semanales.id_dia = $dia
            AND TIME_FORMAT(hora, '%H:%i:%s') BETWEEN '$hora:00:00' AND '$hora:59:00'
        ")->get();


        if($qb == null){
            return Rsp::ok()
                    ->set('ok', true)
                    ->set('rsp', []);
        }
        foreach($qb as $ke){
            if($ke->id_mensaje == $acciones->id_mensaje){
                if($acciones->status == 1){
                    return Rsp::ok()
                    ->set('ok', true)
                    ->set('rsp', []);
                }else{
                    return Rsp::ok()
                        ->set('ok', true)
                        ->set('rsp', $ke);
                }
            }
        }

       

        // $verificar = [];
        // foreach($acciones as $accion){
        //     if(in_array($accion->id_mensaje, $keys)){
        //         if($accion->status != 1){
        //             array_push($verificar, $accion->id_mensaje);
        //         }
        //     }
        // }
        // $arr = [];
        // foreach($qb as $ke){
        //     if(in_array($ke->id_mensaje, $verificar)){
        //         echo json_encode($ke);
        //         // array_push($arr, $ke);
        //     }
        // }
        // return Rsp::ok()
        //     ->set('ok', true)
        //     ->set('rsp', $arr);

    }

    public function getMessageNoUse(Req $req){

        $data = $req->data([
            'id_user' => 'required'
        ]);

        $acciones = QB::query("SELECT ti.slug as modulo, msj.mensaje_accion, msj.productos_seleccionados, msj.retroalimentacion, msj.id as id_mensaje, ad.hora, ad.status, ad.fecha, es.nombre as estado_mensaje FROM accion_diaria ad
            LEFT JOIN mensajes msj ON msj.id = ad.id_mensaje
            LEFT JOIN estados es ON es.id = msj.id_estados
            LEFT JOIN registro reg ON reg.id = ad.id_registro
            LEFT JOIN tienda ti ON ti.id = msj.modulo
            WHERE reg.id_user = $data->id_user
            AND ad.status = 0
            AND ad.fecha >= '" . minusOneDay() . "' AND ad.fecha <= '" . getToday() . "'
        ")->get();

        return Rsp::ok()
                ->set('ok', true)
                ->set('acciones', $acciones);

    }

    public static function getMessageStatic(){
        $hora = date("H");
        // $hora = '14';
        $dia = date('l');
        $dia = Dias::getDay($dia);

        $qb = QB::query("SELECT acciones_semanales.hora, mensajes.mensaje_accion, mensajes.productos_seleccionados, mensajes.retroalimentacion, mensajes.id as id_mensaje FROM acciones_semanales
            LEFT JOIN mensajes ON mensajes.id = acciones_semanales.id_mensaje
            WHERE acciones_semanales.id_dia = $dia
            AND TIME_FORMAT(hora, '%H:%i:%s') BETWEEN '$hora:00:00' AND '$hora:59:00'
        ")->get();

        return $qb;

    }

    public function getMessageByModulo(Req $req){
        $data = $req->data([
            'id_user' => 'required|num',
            'modulo' => 'required'
        ]);

        $modulo = $data->modulo;

        // ID Acciones Completadas
        // $acciones = QB::table('registro')->select(['accion_diaria'])->where('id_user', $data->id_user)->where('fecha', getToday())->get()[0]->accion_diaria;
        // $acciones = json_decode($acciones, true);
        $hora = date("H");
        // $hora = '14';
        $dia = date('l');
        $dia = Dias::getDay($dia);
        $acciones = QB::query("SELECT re.id, ad.status, ad.id_mensaje FROM registro re
            LEFT JOIN accion_diaria ad ON ad.id_registro = re.id
            WHERE re.id_user = $data->id_user
            AND ad.fecha = '".getToday()."'
            AND TIME_FORMAT(ad.hora, '%H:%i:%s') BETWEEN '$hora:00:00' AND '$hora:59:00'
        ")->get()[0];

        $qb = QB::query("SELECT acciones_semanales.hora, mensajes.mensaje_accion, mensajes.productos_seleccionados, mensajes.retroalimentacion, mensajes.id as id_mensaje, tienda.slug as modulo, es.nombre as estado_mensaje FROM acciones_semanales
            LEFT JOIN mensajes ON mensajes.id = acciones_semanales.id_mensaje
            LEFT JOIN estados es ON es.id = mensajes.id_estados
            LEFT JOIN tienda ON tienda.id = mensajes.modulo
            WHERE acciones_semanales.id_dia = $dia
            AND tienda.slug = '$modulo'
            AND TIME_FORMAT(hora, '%H:%i:%s') BETWEEN '$hora:00:00' AND '$hora:59:00'
        ")->get();

        if($qb == null){
            return Rsp::ok()
                    ->set('ok', true)
                    ->set('rsp', []);
        }
        foreach($qb as $ke){
            if($ke->id_mensaje == $acciones->id_mensaje){
                if($acciones->status == 1){
                    return Rsp::ok()
                    ->set('ok', true)
                    ->set('rsp', []);
                }else{
                    return Rsp::ok()
                        ->set('ok', true)
                        ->set('rsp', $ke);
                }
            }
        }
        // echo json_encode($acciones);
        // exit;
        // $keys = [];
        // foreach($qb as $ke){
        //     array_push($keys, $ke->id_mensaje);
        // }

        // if($qb == null){
        //     return Rsp::ok()
        //             ->set('ok', true)
        //             ->set('rsp', []);
        // }

        // $verificar = [];
       
        // foreach($acciones as $accion){
        //     if(in_array($accion['id_mensaje'], $keys)){
        //         if($accion['status'] != 1){
        //             array_push($verificar, $accion['id_mensaje']);
        //         }
        //     }
        // }

        // $arr = [];
        // foreach($qb as $ke){
        //     if(in_array($ke->id_mensaje, $verificar)){
        //         array_push($arr, $ke);
        //     }
        // }
        // return Rsp::ok()
        //     ->set('ok', true)
        //     ->set('rsp', $arr);
        
    }

    public static function getActionsDiary(){
        $dia = date('l');
        $dia = Dias::getDay($dia);
        $qb = QB::query("SELECT acciones_semanales.hora, mensajes.mensaje_accion, mensajes.productos_seleccionados, mensajes.retroalimentacion, mensajes.id as id_mensaje FROM acciones_semanales
            LEFT JOIN mensajes ON mensajes.id = acciones_semanales.id_mensaje
            WHERE acciones_semanales.id_dia = $dia
        ")->get();
        return $qb;
    }

    public static function dormir(Req $req){

        $data = $req->data([
            'id_user' => 'required|num',
            'dormir' => 'required'
        ]);


        QB::table('dormir')
            ->insert([
                'id_user' => $data->id_user,
                'dormir' => $data->dormir,
                'date_created' => getTodayHours(),
                'fecha' => getToday()
            ]);
        
        return Rsp::ok()
                ->set('ok', true);

    }

    public static function getDormir(Req $req){

        $data = $req->data([
            'id_user' => 'required|num'
        ]);

        $qb = QB::table('dormir')
              ->where('id_user', $data->id_user)
              ->orderBy('id',"DESC")
              ->limit(2)
              ->get();

        return Rsp::ok()
                ->set('ok', true)
                ->set('msg', "Información de un día anterior")
                ->set('data', $qb);

    }


}