<?php namespace Models;

use Inc\Bases\BaseModel;

class Dias extends BaseModel{


    const DIA_Monday = 1;
    const DIA_Tuesday = 2;
    const DIA_Wednesday = 3;
    const DIA_Thursday = 4;
    const DIA_Friday = 5;
    const DIA_Saturday = 6;
    const DIA_Sunday = 7;

    public static function getDay($dia){
        switch ($dia) {
            case 'Monday':
                return self::DIA_Monday;
            case 'Tuesday':
                return self::DIA_Tuesday;
            case 'Wednesday':
                return self::DIA_Wednesday;
            case 'Thursday':
                return self::DIA_Thursday;
            case 'Friday':
                return self::DIA_Friday;
            case 'Saturday':
                return self::DIA_Saturday;
            case 'Sunday':
                return self::DIA_Sunday;
            default:
                return null; // O maneja el caso de error de alguna manera
        }
    }

}