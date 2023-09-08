<?php

namespace Controllers\api;

use Inc\Req;
use Inc\Rsp;
use Models\Obstaculo as ModelsObstaculo;

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

}



?>