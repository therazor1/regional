<?php

namespace Controllers\api;

use Inc\Req;
use Inc\Rsp;
use Models\Memoria as ModelsMemoria;
use Libs\Pixie\QB;

class memoria extends _controller {


    public function __construct(){
        parent::__construct(false);
    }


    public function upLevel(Req $req){

        $data = $req->data([
            'id_user' => 'required'
        ]);

        $upLevel = new ModelsMemoria($data->id_user);
        return Rsp::ok()
                ->set('status', $upLevel->state);
    }

    public function getLevel(Req $req){
        $data = $req->data([
            'id_user' => 'required'
        ]);

        $qb = QB::table('memoria')->select(['nivel'])->where('id_user', $data->id_user)->get()[0];
        return Rsp::ok()
                ->set('ok', true)
                ->set('msg', "Nivel actual del juego memoria")
                ->set('nivel', $qb->nivel);

    }

}



?>