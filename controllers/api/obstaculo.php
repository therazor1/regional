<?php

namespace Controllers\api;

use Inc\Req;
use Inc\Rsp;
use Models\Obstaculo as ModelsObstaculo;
use Libs\Pixie\QB;

class obstaculo extends _controller {


    public function __construct(){
        parent::__construct(false);
    }


    public function upLevel(Req $req){

        $data = $req->data([
            'id_user' => 'required'
        ]);

        $upLevel = new ModelsObstaculo($data->id_user);
        return Rsp::ok()
                ->set('status', $upLevel->state);
    }

    public function getLevel(Req $req){
        $data = $req->data([
            'id_user' => 'required'
        ]);

        $qb = QB::table('obstaculo')->select(['nivel'])->where('id_user', $data->id_user)->get()[0];
        return Rsp::ok()
                ->set('ok', true)
                ->set('msg', "Nivel actual del juego obstaculo")
                ->set('nivel', $qb->nivel);

    }

}



?>