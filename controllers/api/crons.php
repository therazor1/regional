<?php namespace Controllers\api;


use Inc\Rsp;
use Libs\Pixie\QB;
use Models\Log;

class crons extends _controller{

    public function __construct(){
        parent::__construct(false);
    }


    public function daily(){

        $usuarios = QB::table('usuarios')->select(['id', 'barra_estado', 'estado_alimentacion', 'estado_salud', 'estado_descanso', 'estado_game'])->get();

        $getAcctionDiary = usuario::getActionsDiary();

        foreach($usuarios as $user){

            $myData = [];
            $myData['id_user '] = $user->id;
            $myData['barra_estado '] = $user->barra_estado;
            $myData['estado_alimentacion '] = $user->estado_alimentacion;
            $myData['estado_salud '] = $user->estado_salud;
            $myData['estado_descanso '] = $user->estado_descanso;
            $myData['estado_game '] = $user->estado_game;
            $myData['date_created'] = date("Y-m-d H:i:s");
            $myData['fecha'] = date("Y-m-d");
            $insertId = QB::table("registro")->insert($myData);
            foreach($getAcctionDiary as $action){
                $acciondiaria = [];
                $acciondiaria['id_registro'] = $insertId;
                $acciondiaria['id_mensaje'] = $action->id_mensaje;
                $acciondiaria['hora'] = $action->hora;
                $acciondiaria['fecha'] = date("Y-m-d");
                $acciondiaria['status'] = 0;
                QB::table("accion_diaria")->insert($acciondiaria);
                $acciondiaria = [];

            }


        }



    }

}