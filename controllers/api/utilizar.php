<?php

namespace Controllers\api;

use Controllers\api\usuario as ApiUsuario;
use Google\Cloud\Dialogflow\V2\Agent\Tier;
use Libs\Pixie\QB;
use Inc\Req;
use Inc\Rsp;
use Models\Inventario;
use Models\Registro;
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
        }else if($data->modulo == "descanso"){
            $slug = "Descanso";
        }else if($data->modulo == 'minijuegos'){
            $slug = "Minijuegos";
        }
        
        $productos = QB::table('tienda_productos tp');
        $productos->select([
            'tp.id',
            'tp.nombre_producto',
            'tp.imagen',
            'tp.puntos_obtenidos',
            'tp.referencias',
            'tp.puntos_requeridos',
        ]);
        $productos->leftJoin('tienda t', 't.id', "=", "tp.id_tienda");
        $productos->where('t.nombre', $slug);
        $productos = $productos->get();

        if($inventario == null){
        
            return Rsp::ok()
                ->set('ok', false)
                ->set('productos' , []);
        }

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

                if($producto->puntos_requeridos == 0){
                    $producto->mostrar = 1;
                }
            }
            unset($producto->puntos_requeridos);
        }
        return Rsp::ok()
                ->set('ok', $productos !== [] ? true : false)
                ->set('productos' , $productos);
    }


    public function use(Req $req){

        
        $messageNow = ApiUsuario::getMessageStatic();

        $id_mensaje = "";
        $data = $req->data([
            'modulo' => 'required',
            'id_usuario' => 'required',
            'id_producto' => 'required',
        ]);
        $seleccionados = [];
        if($messageNow !== null){
            foreach($messageNow as $msg){
                if(in_array($data->id_producto, explode("-",$msg->productos_seleccionados))){
                    $seleccionados = explode("-",$msg->productos_seleccionados);
                    $id_mensaje = $msg->id_mensaje;
                }
            }
            // $seleccionados = explode("-",$messageNow->productos_seleccionados);
        }
        if($seleccionados == []){
            return Rsp::ok()
                ->set('ok', false)
                ->set('msg', "No puede realizar esta accion other product");
        }
        // Instancia de Usuario
        $usuario = new Usuario($data->id_usuario);

        // Instancia Registro
        $registro = new Registro($data->id_usuario);

        // Obtener Puntos
        $puntos = $usuario->getPoints();

        // Obtener inventario de usuario
        $Inventary = new Inventario($data->id_usuario);
        $inventario = $Inventary->getInventary();
        $inventario = json_decode($inventario, true);

        // Obtener acciones diaria
        $accion = $usuario->getActionDiar($id_mensaje);

        $slug = "";
        $mensaje = "";
        $estado = "";
        if($data->modulo == "alimentacion"){
            $slug = Tienda::ALIMENTACION;
            $mensaje = Tienda::msjAlimentacion();
            $estado = "estado_alimentacion";
        }else if ($data->modulo == "salud"){
            $slug = Tienda::SALUD;
            $estado = "estado_salud";
        }else if($data->modulo == "descanso"){
            $slug = Tienda::DESCANSO;
            $estado = "estado_descanso";
        }else if($data->modulo == "minijuegos"){
            $slug = Tienda::MINIJUEGOS;
            $estado = "estado_game";
        }
        if($accion->status == 1){
            return Rsp::ok()
                ->set('ok', false)
                ->set('msg', "Ya realizó esta acción diaria");
        }

        if(!in_array($data->id_producto, $seleccionados)){
            return Rsp::ok()
                ->set('ok', false)
                ->set('msg', "No puede realizar esta accion other product");
        }

        if(intval($inventario[$slug][$data->id_producto]['cantidad']) > 0){
            $nombre_producto = $inventario[$slug][$data->id_producto]['nombre_producto'];
            $inventario[$slug][$data->id_producto]['cantidad'] = intval($inventario[$slug][$data->id_producto]['cantidad']) - 1;
            $energia = $inventario[$slug][$data->id_producto]['energia'];

            $puntos += intval($inventario[$slug][$data->id_producto]['puntos_obtenidos']);
            $msjPuntos = strval($inventario[$slug][$data->id_producto]['puntos_obtenidos']);

            if($inventario[$slug][$data->id_producto]['cantidad'] == 0){
                unset($inventario[$slug][$data->id_producto]);
            }

            // Actualizar status de accion diaria
            $usuario->updateActionDiar($accion);

            // Actualizar Puntos Usuario
            $usuario->updatePoints($puntos);

            // Actualizar Energia Usuario
            $usuario->updateEstadoUser($energia, $estado);
            

            // Actualizar Inventario
            $Inventary->updateInventary($inventario);

            // Reemplzar texto
            $mensaje = str_replace(["AVATAR", "PUNTOS"], [$usuario->avatar, $msjPuntos], $messageNow[0]->retroalimentacion);
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