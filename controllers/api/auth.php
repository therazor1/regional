<?php

namespace Controllers\api;

use Inc\database\QB;
use Inc\database\Raw;
use Inc\Req;
use Inc\Rsp;
use Models\Genero;
use Models\Puntos;
use Models\Personajes;
use Models\Usuario;

class auth extends _controller{


    public function __construct(){
        parent::__construct(false);
    }


    public function inicio(Req $req){

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = $req->data([
                'id_user' => "required",
                'personaje' => 'required',
                'genero' => 'required',
                'avatar' => 'required'
            ]);
            
            $myData = [];
            $myData['id_user'] = $data->id_user;
            $myData['personaje'] = Personajes::getPersonaje($data->personaje);
            $myData['genero'] = Genero::getGenero($data->genero);
            $myData['avatar'] = $data->avatar;
            $myData['age'] = 1;
            $myData['puntos'] = Puntos::INITIAL;
            $myData['barra_estado'] = 50;
            $myData['estado_alimentacion'] = 50;
            $myData['estado_salud'] = 50;
            $myData['estado_descanso'] = 50;
            $myData['estado_game'] = 50;
            $myData['nivel'] = 1;
            
            $insert = QB::table('usuarios');
            $insert = $insert->insert($myData);
 
            if($insert){
                return Rsp::ok()
                    ->set('ok', true)
                    ->set('user', $myData);
            }else{
                return Rsp::e404();
            }

            
        }

    }


    public function verify(Req $req){

        if($_SERVER['REQUEST_METHOD'] === "POST"){

            $data = $req->data([
                'id_user' => 'required'
            ]);

            $verify = QB::table('usuarios');
            $verify->select(['*']);
            $verify->where('id_user', $data->id_user);
            $rsp = $verify->get();

            
            if($rsp !== []){
                $color = Usuario::getStatusColor($rsp[0]->barra_estado);
                $rsp[0]->color = $color;
                return Rsp::ok()
                        ->set('ok', 'ok')
                        ->set('existe', true)
                        ->set('data', $rsp);
            }
            return Rsp::ok()
                    ->set('ok', 'ok')
                    ->set('existe', false); 

        }

    }

}

?>