<?php namespace Models;

use Inc\Bases\BaseModel;
use Libs\Pixie\QB;

class Obstaculo extends BaseModel{


    const NIVEL_1 = 1;
    const POINTS_1 = 30;
    const LOST_1 = 20;

    const NIVEL_2 = 2;
    const POINTS_2 = 42;
    const LOST_2 = 30;

    const NIVEL_3 = 3;
    const POINTS_3 = 54;
    const LOST_3 = 40;

    const NIVEL_4 = 4;
    const POINTS_4 = 66;
    const LOST_4 = 50;

    const NIVEL_5 = 5;
    const POINTS_5 = 84;
    const LOST_5 = 60;


    public $id_user;
    public $state = false;


    public function __construct($id_user){
        $this->id_user = $id_user;
        self::instancia();
    }

    public function instancia(){
        $info = QB::table('obstaculo')->select(['*'])->where('id_user', $this->id_user)->get()[0];
        if(!$info->id_user){
            QB::table('obstaculo')->insert(['id_user' => $this->id_user, 'nivel' => self::NIVEL_1]);
            self::get_puntos(self::NIVEL_1);
            self::updateLevel(self::NIVEL_1);
            return $this->state = true;
        }
        self::get_puntos($info->nivel);
        self::updateLevel($info->nivel);
        return $this->state = true;
    }

    public function get_puntos($nivel){
        $usuario = new Usuario($this->id_user);
        $puntos = intval($usuario->getPoints());
        $puntos = constant("self::POINTS_" . $nivel) + $puntos;
        $usuario->updatePoints($puntos);

        // Energy Lost
        $usuario->updateEstadoUser(constant("self::LOST_" . $nivel), 'estado_game', true);

    }

    public function updateLevel($nivel){
        if($nivel == 10){
            $nivel = 0;
        }
        return QB::table('obstaculo')
            ->where('id_user', $this->id_user)
            ->update(['nivel' => intval($nivel)+1]);
    }

}



?>