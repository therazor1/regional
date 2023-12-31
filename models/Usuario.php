<?php

namespace Models;

use Inc\Bases\BaseModel;
use Libs\Pixie\QB;

class Usuario extends BaseModel{


    public $id;
    public $id_user;
    public $personaje;
    public $genero;
    public $avatar;
    public $age;
    public $puntos;
    public $barra_estado;
    public $estado_alimentacion;
    public $estado_salud;
    public $estado_descanso;
    public $estado_game;
    public $nivel;
    public $color;

    const COLOR_ROJO = "#FF0000";
    const COLOR_AMARILLO = "#FFFF00";
    const COLOR_AZUL = "#0000FF";


    public function __construct($id) {
        $this->id = $id;
        self::instancia();
    }

    public function instancia(){
        $instancia = QB::table('usuarios')
            ->select([
                '*'
            ])
            ->where('id', $this->id)->get()[0];
        $this->id = $instancia->id;
        $this->id_user = $instancia->id_user;
        $this->personaje = $instancia->personaje;
        $this->genero = $instancia->genero;
        $this->avatar = $instancia->avatar;
        $this->age = $instancia->age;
        $this->puntos = $instancia->puntos;
        $this->barra_estado = $instancia->barra_estado;
        $this->estado_alimentacion = $instancia->estado_alimentacion;
        $this->estado_salud = $instancia->estado_salud;
        $this->estado_descanso = $instancia->estado_descanso;
        $this->estado_game = $instancia->estado_game;
        $this->nivel = $instancia->nivel;
    }

    public function getPoints(){
        return intval($this->puntos);
    }

    public function updatePoints($puntos){
        return QB::table('usuarios')
            ->where('id', $this->id)
            ->update(['puntos' => $puntos]);
    }

    public function updateEstadoUser($porcentaje, $estado, $percent = false){
        
        if(!$percent){
            $this->{$estado} += $porcentaje;
        }
        $this->{$estado} = ($this->{$estado} - ($this->{$estado} * ($porcentaje / 100)));
        self::mediaEstado();

        QB::table('registro')
            ->where('id_user', $this->id)
            ->where('fecha', getToday())
            ->update([
                'barra_estado' => $this->barra_estado,
                "$estado" => $this->{$estado},
            ]);

        return QB::table('usuarios')
                ->where('id', $this->id)
                ->update([
                    'barra_estado' => $this->barra_estado,
                    "$estado" => $this->{$estado},
                    'nivel' => $this->nivel
                ]);
        
    }

    public function mediaEstado(){
        $sum = round(($this->estado_alimentacion + $this->estado_salud + $this->estado_descanso + $this->estado_game)/4);
        $this->barra_estado = $sum;
        self::verificarNivel();
    }

    public function verificarNivel(){
        if($this->barra_estado >= 100){
            $this->nivel += 1;
        }
    }

    public static function getStatusColor($barra_estado){
        return ($barra_estado <= 50) ? self::COLOR_ROJO : (($barra_estado <= 75) ? self::COLOR_AMARILLO : self::COLOR_AZUL);
    }

    public function getActionDiar($id_mensaje){
        $hora = date("H");
        $accion = QB::query("SELECT ad.id_mensaje, ad.id as id_accion, ad.status FROM accion_diaria ad
            LEFT JOIN registro reg ON reg.id = ad.id_registro
            WHERE reg.id_user = $this->id
            AND ad.id_mensaje = $id_mensaje
            AND TIME_FORMAT(hora, '%H:%i:%s') BETWEEN '$hora:00:00' AND '$hora:59:00'
            AND ad.fecha = '".getToday()."'
        ")->get()[0];
        return $accion;
    }

    public function updateActionDiar($action){
        return QB::table('accion_diaria')
                ->where('id', $action->id_accion)
                ->where('fecha', getToday())
                ->update([
                    'status' => 1
                ]);
    }

    public static function getOneMessage($id_action){

        $qb = QB::query("SELECT ad.id as id_accion, ad.hora, es.nombre as estado_mensaje, msj.id as id_mensaje, msj.retroalimentacion, msj.mensaje_accion, msj.productos_seleccionados FROM accion_diaria ad
            LEFT JOIN mensajes msj ON msj.id = ad.id_mensaje
            LEFT JOIN estados es ON es.id = msj.id_estados
            WHERE ad.id = $id_action
        ")->get();

        return $qb;
    }


}

?>