<?php

namespace Controllers\api;

use Inc\database\QB;
use Inc\Req;
use Inc\Rsp;

class tienda extends _controller{

    public function __construct(){
        parent::__construct(false);
    }

    public function create(Req $req){

        $data = $req->data([
            'nombre' => 'required',
            'slug' => 'required'
        ]);

        $myData['nombre'] = $data->nombre;
        $myData['slug'] = $data->slug;

        $qb = QB::table('tienda');
        $insert = $qb->insert($myData);

        if($insert){
            return Rsp::ok()
                ->set('ok', "ok")
                ->set('data', $myData);
        }else{
            return Rsp::e404();
        }

    }


}


?>