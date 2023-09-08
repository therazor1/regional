<?php namespace Controllers\api;

use Inc\Req;
use Inc\database\QB;

class mensaje extends _controller {

    public function __construct(){
        parent::__construct(false);
    }


    public function create(Req $req){

        $data = $req->data([
            'modulo'                  => 'num|required',
            'mensaje_accion'          => 'required',
            'frecuencia'              => 'required',
            'cantidad_mensaje'        => 'required',
            'productos_seleccionados' => 'required',
            'retroalimentacion'       => 'required',
            'mensajes_final'          => 'required'
        ]);

        $myData = [];
        $myData['modulo'] = $data->modulo;
        $myData['mensaje_accion'] = $data->mensaje_accion;
        $myData['frecuencia'] = $data->frecuencia;
        $myData['cantidad_mensaje'] = $data->cantidad_mensaje;
        $myData['productos_seleccionados'] = $data->productos_seleccionados;
        $myData['retroalimentacion'] = $data->retroalimentacion;
        $myData['mensajes_final'] = $data->mensajes_final;

        $rsp = QB::table('mensajes')->insert($myData);
        echo json_encode($rsp);

    }

}