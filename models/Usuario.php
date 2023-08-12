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
    }

    public function getPoints(){
        return intval($this->puntos);
    }

    public function updatePoints($puntos){
        return QB::table('usuarios')
            ->where('id', $this->id)
            ->update(['puntos' => $puntos]);
    }


}

?>