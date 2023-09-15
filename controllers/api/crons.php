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
            foreach($getAcctionDiary as $action){
                $acct[$action->id_mensaje] = array(
                    'hora' => $action->hora,
                    'status' => 0,
                    'id_mensaje' => $action->id_mensaje
                );
            }
            $acct = json_encode($acct);
            $myData['accion_diaria'] = $acct;
            $acct = [];

            QB::table("registro")->insert($myData);



        }



    }

}