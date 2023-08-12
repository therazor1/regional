<?php

namespace Controllers\api;

use Libs\Pixie\QB;
use Inc\Req;
use Inc\Rsp;
use Models\Inventario;
use Models\Tienda;
use Models\Usuario;

class utilizar extends _controller{

    public function __construct(){
        parent::__construct(false);
    }


    public function index(Req $req){

        $data = $req->data([
            'modulo' => 'required',
            'id_usuario' => 'required'
        ]);

        // Obtener inventario de usuario
        $inventario = QB::table('inventario')->select(['content'])->where('id_usuario', $data->id_usuario)->get()[0]->content;
        $inventario = json_decode($inventario, true);
        $slug = "";
        if($data->modulo == "alimentacion"){
            $slug = "Alimentación";
        }else if ($data->modulo == "salud"){
            $slug = "Salud y aseo";
        }
        
        $productos = QB::table('tienda_productos tp');
        $productos->select([
            'tp.id',
            'tp.nombre_producto'
        ]);
        $productos->leftJoin('tienda t', 't.id', "=", "tp.id_tienda");
        $productos->where('t.nombre', $slug);
        $productos = $productos->get();

        $keys = array_keys($inventario[$slug]);
        foreach ($productos as $producto) {
            if($keys == null){
                $producto->mostrar = 0;
            }else{
                $id = intval($producto->id); 
                if(in_array($id, $keys)){
                    $producto->mostrar = 1;
                }else{
                    $producto->mostrar = 0;
                }
            }
        }
        return Rsp::ok()
                ->set('ok', true)
                ->set('productos' , $productos);
    }


    public function use(Req $req){
        $data = $req->data([
            'modulo' => 'required',
            'id_usuario' => 'required',
            'id_producto' => 'required',
        ]);
        // Instancia de Usuario
        $usuario = new Usuario($data->id_usuario);

        // Obtener Puntos
        $puntos = $usuario->getPoints();

        // Obtener inventario de usuario
        $Inventary = new Inventario($data->id_usuario);
        $inventario = $Inventary->getInventary();
        $inventario = json_decode($inventario, true);


        $slug = "";
        $mensaje = "";
        if($data->modulo == "alimentacion"){
            $slug = Tienda::ALIMENTACION;
            $mensaje = Tienda::msjAlimentacion();
        }else if ($data->modulo == "salud"){
            $slug = Tienda::SALUD;
        }
        if(intval($inventario[$slug][$data->id_producto]['cantidad']) > 0){
            $nombre_producto = $inventario[$slug][$data->id_producto]['nombre_producto'];
            $inventario[$slug][$data->id_producto]['cantidad'] = intval($inventario[$slug][$data->id_producto]['cantidad']) - 1;
    
            $puntos += intval($inventario[$slug][$data->id_producto]['puntos_obtenidos']);
            $msjPuntos = strval($inventario[$slug][$data->id_producto]['puntos_obtenidos']);

            if($inventario[$slug][$data->id_producto]['cantidad'] == 0){
                unset($inventario[$slug][$data->id_producto]);
            }

            // Actualizar Puntos Usuario
            $usuario->updatePoints($puntos);
            
            // Actualizar Inventario
            $inventario = json_encode($inventario);
            $Inventary->updateInventary($inventario);

            
            // Reemplzar texto
            $mensaje = str_replace(["XXX", "YYY"], [$nombre_producto, $msjPuntos], $mensaje);
            return Rsp::ok()
                    ->set('ok', true)
                    ->set('msg', $mensaje);
        }else{
            return Rsp::ok()
                   ->set('ok', false)
                   ->set('msg', "No puede realizar esta accion");
        }
        


    }

}


?>