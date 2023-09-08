<?php

namespace Controllers\api;

use Inc\Req;
use Inc\Rsp;
use Models\Memoria as ModelsMemoria;

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

}



?>