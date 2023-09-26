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

        // ID Acciones Completadas
        $acciones = QB::table('registro')->select(['accion_diaria'])->where('id_user', $data->id_user)->where('fecha', getToday())->get()[0]->accion_diaria;
        $acciones = json_decode($acciones, true);
        $hora = date("H");
        // $hora = '14';
        $dia = date('l');
        $dia = Dias::getDay($dia);

        $qb = QB::query("SELECT acciones_semanales.hora, mensajes.mensaje_accion, mensajes.productos_seleccionados, mensajes.retroalimentacion, mensajes.id as id_mensaje FROM acciones_semanales
            LEFT JOIN mensajes ON mensajes.id = acciones_semanales.id_mensaje
            WHERE acciones_semanales.id_dia = $dia
            AND TIME_FORMAT(hora, '%H:%i:%s') BETWEEN '$hora:00:00' AND '$hora:59:00'
        ")->get();

        $keys = [];
        foreach($qb as $ke){
            array_push($keys, $ke->id_mensaje);
        }

        if($qb == null){
            return Rsp::ok()
                    ->set('ok', false)
                    ->set('rsp', []);
        }

        $verificar = [];
       
        foreach($acciones as $accion){
            if(in_array($accion['id_mensaje'], $keys)){
                if($accion['status'] != 1){
                    array_push($verificar, $accion['id_mensaje']);
                }
            }
        }

        $arr = [];
        foreach($qb as $ke){
            if(in_array($ke->id_mensaje, $verificar)){
                array_push($arr, $ke);
            }
        }
        return Rsp::ok()
            ->set('ok', true)
            ->set('rsp', $arr);

    }

    public function getMessageNoUse(Req $req){

        $data = $req->data([
            'id_user' => 'required'
        ]);

        $acciones = QB::table('registro')
                        ->select(['accion_diaria', 'fecha'])
                        ->where('id_user', $data->id_user)
                        ->whereBetween('fecha', minusOneDay(), getToday())
                        ->get();
        $listaAccionesNoUse = array();
        foreach($acciones as $action){
            $fecha = $action->fecha;
            $acc = json_decode($action->accion_diaria, true);
            foreach($acc as $ac){
                if($ac['status'] == 0){
                    if(compareHours($ac['hora'])){
                        var_dump([$fecha][$ac]);
                        // $listaAccionesNoUse[$fecha][$ac] = 1;
                        // var_dump($listaAccionesNoUse[$fecha]);
                        // echo "true";
                    }
                }
            }
        }
        echo json_encode($listaAccionesNoUse);
        $hora = date("H:i");
        echo $hora;
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
        $acciones = QB::table('registro')->select(['accion_diaria'])->where('id_user', $data->id_user)->where('fecha', getToday())->get()[0]->accion_diaria;
        $acciones = json_decode($acciones, true);
        $hora = date("H");
        // $hora = '14';
        $dia = date('l');
        $dia = Dias::getDay($dia);

        $qb = QB::query("SELECT acciones_semanales.hora, mensajes.mensaje_accion, mensajes.productos_seleccionados, mensajes.retroalimentacion, mensajes.id as id_mensaje, tienda.slug as modulo FROM acciones_semanales
            LEFT JOIN mensajes ON mensajes.id = acciones_semanales.id_mensaje
            LEFT JOIN tienda ON tienda.id = mensajes.modulo
            WHERE acciones_semanales.id_dia = $dia
            AND tienda.slug = '$modulo'
            AND TIME_FORMAT(hora, '%H:%i:%s') BETWEEN '$hora:00:00' AND '$hora:59:00'
        ")->get();
        $keys = [];
        foreach($qb as $ke){
            array_push($keys, $ke->id_mensaje);
        }

        if($qb == null){
            return Rsp::ok()
                    ->set('ok', true)
                    ->set('rsp', []);
        }

        $verificar = [];
       
        foreach($acciones as $accion){
            if(in_array($accion['id_mensaje'], $keys)){
                if($accion['status'] != 1){
                    array_push($verificar, $accion['id_mensaje']);
                }
            }
        }

        $arr = [];
        foreach($qb as $ke){
            if(in_array($ke->id_mensaje, $verificar)){
                array_push($arr, $ke);
            }
        }
        return Rsp::ok()
            ->set('ok', true)
            ->set('rsp', $arr);
        
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