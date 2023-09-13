<?php namespace Controllers\api;

use Models\Dias;
use Libs\Pixie\QB;
use Inc\Req;
use Inc\Rsp;

class usuario extends _controller{


    public function __construct(){
        parent::__construct(false);
    }


    public static function getMessage(){

        $hora = date("H");
        // $hora = '14';
        $dia = date('l');
        $dia = Dias::getDay($dia);

        $qb = QB::query("SELECT acciones_semanales.hora, mensajes.mensaje_accion, mensajes.productos_seleccionados, mensajes.retroalimentacion, mensajes.id as id_mensaje FROM acciones_semanales
            LEFT JOIN mensajes ON mensajes.id = acciones_semanales.id_mensaje
            WHERE acciones_semanales.id_dia = $dia
            AND TIME_FORMAT(hora, '%H:%i:%s') BETWEEN '$hora:00:00' AND '$hora:59:00'
        ")->get()[0];
        return $qb;

    }


}