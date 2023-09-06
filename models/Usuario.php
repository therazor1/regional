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

    public function updateEstadoUser($porcentaje, $estado){
        $this->{$estado} += $porcentaje;
        self::mediaEstado();
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



}

?>