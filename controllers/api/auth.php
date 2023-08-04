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
            $myData['puntos'] = Puntos::INITIAL;
            
            $insert = QB::table('usuarios');
            $insert = $insert->insert($myData);
 
            if($insert){
                return Rsp::ok()
                    ->set('ok', "ok")
                    ->set('data', $myData);
            }else{
                return Rsp::e404();
            }

            
        }

    }

}

?>