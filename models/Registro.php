<?php

namespace Models;

use Inc\Bases\BaseModel;
use Libs\Pixie\QB;

class Registro extends BaseModel{


    public $id;
    public $id_user;
    public $barra_estado;
    public $estado_alimentacion;
    public $estado_salud;
    public $estado_descanso;
    public $estado_game;
    public $date_created;
    public $fecha;


    public function __construct($id) {
        $this->id = $id;
        self::instancia();
    }

    public function instancia(){
        $fecha = date("Y-m-d");
        $instancia = QB::table('registro')
            ->select([
                '*'
            ])
            ->where('id_user', $this->id)
            ->where('fecha', "$fecha")
            ->get()[0];
        $this->id = $instancia->id;
        $this->id_user = $instancia->id_user;
        $this->barra_estado = $instancia->barra_estado;
        $this->estado_alimentacion = $instancia->estado_alimentacion;
        $this->estado_salud = $instancia->estado_salud;
        $this->estado_descanso = $instancia->estado_descanso;
        $this->estado_game = $instancia->estado_game;
        $this->date_created = $instancia->date_created;
        $this->fecha = $instancia->fecha;
    }


    public function updateEstadoRegistro($porcentaje, $estado){
        $this->{$estado} += $porcentaje;
        self::mediaEstado();
        return QB::table('registro')
                ->where('id_user', $this->id_user)
                ->where('fecha', $this->fecha)
                ->update([
                    'barra_estado' => $this->barra_estado,
                    "$estado" => $this->{$estado}
                ]);
    }

    public function mediaEstado(){
        $sum = round(($this->estado_alimentacion + $this->estado_salud + $this->estado_descanso + $this->estado_game)/4);
        $this->barra_estado = $sum;
    }


}

?>