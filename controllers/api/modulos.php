<?php

namespace Controllers\api;

use Inc\database\QB;
use Inc\Req;
use Inc\Rsp;

class modulos extends _controller{

    public function __construct(){
        parent::__construct(false);
    }

    public function index(Req $req){

        if($_SERVER['REQUEST_METHOD'] === 'GET') {

            $data = $req->data([
                'slug' => 'default:'
            ]);
            
            $qb = QB::table('tienda_productos');
            $qb->select([
                '*'
            ]);

            if($data->slug != ""){
                $tiendaId = QB::table('tienda');
                $tiendaId->select(['id']);
                $tiendaId->where('slug', $data->slug);
                $tiendaId = $tiendaId->get()[0]->id;
                $qb->where('id_tienda', $tiendaId);
            }
            $productos = $qb->get();
            return Rsp::ok()
                    ->set('productos', $productos);

        }

    }
   
}



?>