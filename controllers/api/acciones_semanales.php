<?php namespace Controllers\api;

use Inc\Req;
use Inc\Rsp;
use Libs\Pixie\QB;


class acciones_semanales extends _controller{


    public function __construct(){
        parent::__construct(false);
    }


    public function create(Req $req){

        $data = $req->data([
            'hora' => 'required',
            'id_dia' => 'required|num',
            'id_mensaje' => 'required|num'
        ]);


        $qb = QB::table('acciones_semanales')->insert([
            'hora' => $data->hora,
            'id_dia' => $data->id_dia,
            'id_mensaje' => $data->id_mensaje
        ]);

        return Rsp::ok()
                ->set('msg', "Creado")
                ->set('accion', $qb);


    }


}