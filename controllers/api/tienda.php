<?php

namespace Controllers\api;

use Libs\Pixie\QB;
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

    public function inventario(Req $req){

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $req->data([
                'id_usuario' => 'required'
            ]);

            $inventario = QB::table('inventario')->select(['content'])->where('id_usuario', $data->id_usuario)->get()[0]->content;
            $inventario = json_decode($inventario, true);
            echo json_encode($inventario);
        }

    }


    public function compras(Req $req){

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = $req->data([
                'id_usuario' => 'required|num',
                'id_producto' => 'required|num'
            ]);

            $qb = QB::table('inventario');
            $qb->select([
                'content'
            ]);
            $qb->where('id_usuario', $data->id_usuario);
            $info = $qb->get();

            if($info == []){
                $rsp = self::createInventary($data);
                return $rsp;
            }

            // Get info 
            $inf = self::getInfoUserInventary($data); 
            $pointsUser = intval($inf->puntos_usuario);
            $pointsRequired = intval($inf->puntos_requeridos);
            
            if(!validatePoints($pointsUser, $pointsRequired)){
                return Rsp::ok()
                        ->set('ok', false)
                        ->set('msg', 'No tienes los suficientes puntos para adquirir este producto');
            }

            // Get inventary
            $inventario = QB::table('inventario')->select(['content'])->where('id_usuario', $data->id_usuario)->get()[0]->content;
            $inventario = json_decode($inventario, true);


            // Nuevo arreglo invetario
            $newInventary = self::newInventary($data->id_usuario, $inventario, $inf);
            $newInventary = json_encode($newInventary);

            $rsp = QB::table('inventario')->where('id_usuario', $data->id_usuario)->update(['content' => $newInventary]);
            

            if($rsp){
                return Rsp::ok()
                        ->set('ok', true)
                        ->set('msg', "Se agregó correctamente el producto a tu inventario");
            }else{
                return Rsp::e404();
            }

        }

    }


    public function createInventary($data){

        $inf = self::getInfoUserInventary($data);
        $pointsUser = intval($inf->puntos_usuario);
        $pointsRequired = intval($inf->puntos_requeridos);
        
        if(!validatePoints($pointsUser, $pointsRequired)){
            return Rsp::ok()
                    ->set('ok', false)
                    ->set('msg', 'No tienes los suficientes puntos para adquirir este producto');
        }
        // echo json_encode($inf);
        $inventary[$inf->modulo] = array(
            $inf->id_producto => array(
                'id_producto' => $inf->id_producto,
                'nombre_producto' => $inf->nombre_producto,
                'imagen' => $inf->imagen,
                'puntos_obtenidos' => $inf->puntos_obtenidos,
                'cantidad' => 1
            )
        );

        $inventary = json_encode($inventary);

        
        QB::table('usuarios')
            ->where('id', $data->id_usuario)
            ->update([
                'puntos' => minusPoints($inf->puntos_usuario, $inf->puntos_requeridos)
            ]);
        

        $myData['id_usuario'] = $data->id_usuario;    
        $myData['content'] = $inventary;    
        
        $rsp = boolval(QB::table('inventario')->insert($myData));

        if($rsp){
            return Rsp::ok()
                    ->set('ok', true)
                    ->set('msg', "Se agregó correctamente el producto a tu inventario");
        }else{
            return Rsp::e404();
        }

    }


    public function getInfoUserInventary($data){
        $qb = QB::table('tienda_productos tp');
        $qb->select([
            't.nombre modulo',
            'tp.id id_producto',
            'tp.nombre_producto',
            'tp.imagen',
            'tp.puntos_requeridos',
            'tp.puntos_obtenidos',
            'us.puntos puntos_usuario'
        ]);
        $qb->leftJoin('usuarios us', 'us.id', '=', $data->id_usuario);
        $qb->leftJoin('tienda t', 't.id', '=', 'tp.id_tienda');
        $qb->where('tp.id', $data->id_producto);
        return $qb->get()[0];
    }

    public function newInventary($idUser, $inventario, $inf){
        $modulo = $inf->modulo;
        // echo json_encode($inf);
        if(array_key_exists($modulo, $inventario)){
            if(array_key_exists($inf->id_producto, $inventario[$modulo])){
                $inventario[$modulo][$inf->id_producto]['cantidad'] += 1;
            }else{
                $inventario[$inf->modulo][$inf->id_producto] = array(
                    'id_producto' => $inf->id_producto,
                    'nombre_producto' => $inf->nombre_producto,
                    'imagen' => $inf->imagen,
                    'puntos_obtenidos' => $inf->puntos_obtenidos,
                    'cantidad' => 1
                );
            }
        }else{
            $inventario[$inf->modulo] = array(
                $inf->id_producto => array(
                    'id_producto' => $inf->id_producto,
                    'nombre_producto' => $inf->nombre_producto,
                    'imagen' => $inf->imagen,
                    'puntos_obtenidos' => $inf->puntos_obtenidos,
                    'cantidad' => 1
                )
            );
        }

        QB::table('usuarios')->where('id', $idUser)->update(['puntos' => minusPoints($inf->puntos_usuario, $inf->puntos_requeridos)]);

        return $inventario;

    }

}


?>