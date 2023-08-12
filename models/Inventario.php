<?php

namespace Models;

use Inc\Bases\BaseModel;
use Libs\Pixie\QB;

class Inventario extends BaseModel{

    public $id;
    public $id_usuario;
    public $content;


    function __construct($id_usuario){
        $this->id_usuario = $id_usuario;
        self::instancia();
    }

    public function instancia(){
        $instancia = QB::table('inventario')
            ->select(['*'])
            ->where('id_usuario', $this->id_usuario)
            ->get()[0];

        $this->id = $instancia->id;
        $this->content = $instancia->content;

    }


    public function getInventary(){
        return  $this->content;
    }


    public function updateInventary($inventary = ""){
        return QB::table('inventario')
            ->where('id_usuario', $this->id_usuario)
            ->update(['content' => $inventary]);
    }

}


?>