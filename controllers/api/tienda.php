<?php

namespace Controllers\api;

use Inc\database\QB;
use Inc\Req;
use Inc\Rsp;

class tienda extends _controller{

    public function __construct(){
        parent::__construct(false);
    }

    public function index(){

        if($_SERVER['REQUEST_METHOD'] === 'GET') {

            $qb = QB::table('tienda');
            $qb->select([
                '*'
            ]);
            $data = $qb->get();
            return Rsp::ok()
                    ->set('tienda', $data);
        }

    }

    public function create(Req $req){
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    public function create_products(Req $req){

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = $req->data([
                'id_tienda' => 'required|num',
                'nombre_producto' => 'required',
                'puntos_requeridos' => 'required|num',
                'puntos_obtenidos' => 'required|num'
            ]);
            
            $myData['id_tienda'] = $data->id_tienda;
            $myData['nombre_producto'] = $data->nombre_producto;
            $myData['puntos_requeridos'] = $data->puntos_requeridos;
            $myData['puntos_obtenidos'] = $data->puntos_obtenidos;
    
            $qb = QB::table('tienda_productos');
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


    public function compras(){

        if($_SERVER['REQUEST_METHOD'] === 'POST') {



        }

    }


}


?>