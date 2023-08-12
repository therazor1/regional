<?php

namespace Controllers\api;

use Inc\database\QB;
use Inc\database\Raw;
use Inc\Req;
use Inc\Rsp;
use Models\Genero;
use Models\Puntos;
use Models\Personajes;

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
            
            $insert = QB::table('usuarios');
            $insert = $insert->insert($myData);
 
            if($insert){
                return Rsp::ok()
                    ->set('ok', true)
                    ->set('id_user', $myData['id_user'])
                    ->set('personaje', $myData['personaje'])
                    ->set('genero', $myData['genero'])
                    ->set('avatar', $myData['avatar'])
                    ->set('age', $myData['age'])
                    ->set('puntos', $myData['puntos'])
                    ->set('barra_estado', $myData['barra_estado']);
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