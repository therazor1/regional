<?php 

namespace Models;


class Personajes {

    const ZEN = "Zen";
    const LUI = "Lui";

    public static function getPersonaje($personaje){
        if($personaje == "zen"){
            return self::ZEN;
        }else if($personaje == "lui"){
            return self::LUI;
        }
    }

}



?>