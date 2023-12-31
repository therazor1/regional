<?php namespace Models;

use Inc\Bases\BaseModel;
use Libs\Pixie\QB;

class Memoria extends BaseModel{


    const NIVEL_1 = 1;
    const POINTS_1 = 12;
    const LOST_1 = 5;

    const NIVEL_2 = 2;
    const POINTS_2 = 18;
    const LOST_2 = 10;

    const NIVEL_3 = 3;
    const POINTS_3 = 24;
    const LOST_3 = 15;

    const NIVEL_4 = 4;
    const POINTS_4 = 30;
    const LOST_4 = 20;

    const NIVEL_5 = 5;
    const POINTS_5 = 36;
    const LOST_5 = 25;
    
    const NIVEL_6 = 6;
    const POINTS_6 = 42;
    const LOST_6 = 30;

    const NIVEL_7 = 7;
    const POINTS_7 = 48;
    const LOST_7 = 35;

    const NIVEL_8 = 8;
    const POINTS_8 = 54;
    const LOST_8 = 40;

    const NIVEL_9 = 9;
    const POINTS_9 = 60;
    const LOST_9 = 45;

    const NIVEL_10 = 10;
    const POINTS_10 = 72;
    const LOST_10 = 50;


    public $id_user;
    public $state = false;


    public function __construct($id_user){
        $this->id_user = $id_user;
        self::instancia();
    }

    public function instancia(){
        $info = QB::table('memoria')->select(['*'])->where('id_user', $this->id_user)->get()[0];
        if(!$info->id_user){
            QB::table('memoria')->insert(['id_user' => $this->id_user, 'nivel' => self::NIVEL_1]);
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
        return QB::table('memoria')
            ->where('id_user', $this->id_user)
            ->update(['nivel' => intval($nivel)+1]);
    }

}



?>