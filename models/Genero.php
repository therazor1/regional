<?php

namespace Models;


class Genero {

    const NINO = "Niño";
    const NINA = "Niña";


    public static function getGenero($genero = self::NINO){
        if($genero == "niño"){
            return self::NINO;
        }else if($genero == "niña"){
            return self::NINA;
        }
    }

}



?>