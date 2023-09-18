<?php 

namespace Models;


class Personajes {

    const ZEN = "Zen";
    const LOUI = "Loui";

    public static function getPersonaje($personaje){
        if($personaje == "zen"){
            return self::ZEN;
        }else if($personaje == "loui"){
            return self::LOUI;
        }
    }

}



?>