<?php namespace Models;

use Inc\Bases\BaseModel;

class Dream extends BaseModel
{
    const state = NULL;
    const CALIDAD_BIEN    = '1';
    const CALIDAD_REGULAR = '2';
    const CALIDAD_MAL     = '3';

    const FASE_LIGERO    = 1;
    const FASE_PROFUNDO  = 2;
    const FASE_REM       = 3;
    const FASE_DESPIERTO = 4;

    const STATE_APTO    = 'APTO';
    const STATE_NO_APTO    = 'NO APTO';
    const STATE_OBSERVADO    = 'OBSERVADO';
    const SI_EXIST    = 1;
    const NO_EXIST    = 0;

    public $id;
    public $inicio_dormir;
    public $fin_dormir;
    public $horas_dormidas;
    public $calidad;
    public $ligero;
    public $profundo;
    public $rem;
    public $horas_despierto;
    public $id_user;
    public $observacion;
    public $sincronizado_hoy;

    /**
     * PARAMETROS PARA CALCULAR CALIFICACION DREAMS
     */
    const PORC_REM_APTO=19.5;
    const PORC_REM_NO_APTO=14.5;
    const PORC_PROFUNDO_APTO=9.5;
    const PORC_PROFUNDO_NO_APTO=6.5;


    public static function numEntre100($value){
        return $value/100;
    }

    public static function calidades()
    {
        return [

            self::CALIDAD_BIEN    => new State('#F2784B', 'Bien'),
            self::CALIDAD_REGULAR => new State('#C49F47', 'Regular'),
            self::CALIDAD_MAL     => new State('#C49F47', 'Mal')
        ];
    }

    /**
     * CALIFICACION ONLY HORAS_DORMIDAS -> RETURN STATE
     */
    public static function getCalificacionHorasDormidasObj($horas_dormidas_decimal, $exists_data, $onlyTxt=false)
    {
        if ($exists_data) {
            $horas    = floatval($horas_dormidas_decimal);
            if($onlyTxt){
                if ($horas >= 7) {
                    return 'APTO';
                } else if ($horas < 6) {
                    return 'NO APTO';
                } else {
                    return 'OBSERVADO';
                }
            }else{
                if ($horas >= 7) {
                    return new State(self::COLOR_VERDE, 'APTO');
                } else if ($horas < 6) {
                    return new State(self::COLOR_ROJO, 'NO APTO');
                } else {
                    return new State(self::COLOR_AMARILLO, 'OBSERVADO');
                }
            }
        }
    }

    /**
     * CALIFICACION GENERAL -> HORAS DORMIDAS + REM + PROFUNDO
     */

    public static function getCalificacionGeneralHorasDormidasObj($horas_dormidas_decimal, $rem, $profundo, $exists_data)
    {
        //horas_dormidas is decimal
        if ($exists_data) {
            /**
             *$horas_dormidas ->100%
             * calificacion: APTO, NO APTO, OBSERVADO
             */
            $rta_str = '';
            //$rta_detail="Horas de sueño: ".$horas_dormidas."\nREM: ".$rem."\nProfundo: ".$profundo;
            $horas    = floatval($horas_dormidas_decimal);
            $rem      = self::horaToDecimal($rem);
            $profundo = self::horaToDecimal($profundo);
            if ($horas >= 7 && $rem >= $horas * 0.2 && $profundo >= $horas * 0.1) {
                $rta_str = 'APTO';
                return new State(self::COLOR_VERDE, $rta_str);
            } else if ($horas < 6 && $rem < $horas * 0.15 && $profundo < $horas * 0.07) {
                $rta_str = 'NO APTO';
                return new State(self::COLOR_ROJO, $rta_str);
            } else {
                $rta_str = 'OBSERVADO';
                return new State(self::COLOR_AMARILLO, $rta_str);
            }
        }
    }

    /**
     * $calificacion_profundo: APTO
     * $calificacion_rem     : NO APTO
     * return String Label + Color
     */
    public static function getPeorCalificacionGeneralTxt($calificacion_horas_dormidas, $calificacion_profundo, $calificacion_rem, $exists_data, $onlyTxt=false)
    {
        if ($exists_data) {
            if (!empty($calificacion_profundo) && !empty($calificacion_horas_dormidas)) {
                //TODO: El maximo es el peor -> NO APTO
                $horaDormidasNum = self::converterCalificacionTextNumero($calificacion_horas_dormidas);
                $profundoNum = self::converterCalificacionTextNumero($calificacion_profundo);
                $remNum      = self::converterCalificacionTextNumero($calificacion_rem);
                return self::converterCalificacionNumeroText(max($horaDormidasNum, $profundoNum, $remNum), $onlyTxt);
            }else{
                return 'Sin calificación';
            }
        } else {
            return '';
        }
    }

    /**
     * @param $horas_dormidas_decimal: 8.0
     * @param $profundo: 04:12
     * @param $rem : 04:12
     * @param $exists_data
     * @param bool $onlyTxt
     * @return State|string
     */
    public static function getPeorCalificacionGeneralTxtDinamico(
        $horas_dormidas_decimal,
        $profundo,
        $rem,
        $exists_data,
        bool $onlyTxt = false
    )
    {
        if ($exists_data) {
            $horasDormidasTxt=self::getCalificacionHorasDormidasObj($horas_dormidas_decimal,$exists_data,true);
            $calificacionProfundoTxt=self::calificacionProfundo($horas_dormidas_decimal,$profundo, $exists_data)->name;
            $calificacionRemTxt=self::calificacionREM($horas_dormidas_decimal,$rem, $exists_data)->name;

            if (!empty($calificacionProfundoTxt) && !empty($calificacionRemTxt)) {

                //TODO: El maximo es el peor -> NO APTO
                $horaDormidasNum = self::converterCalificacionTextNumero($horasDormidasTxt);
                $profundoNum = self::converterCalificacionTextNumero($calificacionProfundoTxt);
                $remNum      = self::converterCalificacionTextNumero($calificacionRemTxt);
                return self::converterCalificacionNumeroText(max($horaDormidasNum, $profundoNum, $remNum), $onlyTxt);
            }else{
                return 'Sin calificación';
            }
        } else {
            return '';
        }
    }

    /**
     * 1 PROFUNDO -> ETIQUETA TEXT + COLOR
     */
    public static function calificacionProfundo($horas_dormidas_decimal, $profundo, $exists_data)
    {
        if ($exists_data) {
            $horas    = floatval($horas_dormidas_decimal);
            $profundo = self::horaToDecimal($profundo);
            if ($profundo >= $horas * self::numEntre100(self::PORC_PROFUNDO_APTO)) { //0.1 -> now: 0.095
                $rta_str = 'APTO';
                return new State(self::COLOR_VERDE, $rta_str);
            } else if ($profundo < $horas * self::numEntre100(self::PORC_PROFUNDO_NO_APTO)) { //0.07 -> now: 0.065
                $rta_str = 'NO APTO';
                return new State(self::COLOR_ROJO, $rta_str);
            } else {
                $rta_str = 'OBSERVADO';
                return new State(self::COLOR_AMARILLO, $rta_str);
            }
        }
    }

    /**
     * 2 REM -> ETIQUETA TEXT + COLOR
     */
    public static function calificacionREM($horas_dormidas_decimal, $rem, $exists_data)
    {
        if ($exists_data) {
            $horas = floatval($horas_dormidas_decimal);
            $rem   = self::horaToDecimal($rem);
            if ($rem >= $horas * self::numEntre100(self::PORC_REM_APTO)) { //antes: 0.2 -> now 0.195
                $rta_str = 'APTO';
                return new State(self::COLOR_VERDE, $rta_str);
            } else if ($rem < $horas * self::numEntre100(self::PORC_REM_NO_APTO)) { //0.15
                $rta_str = 'NO APTO';
                return new State(self::COLOR_ROJO, $rta_str);
            } else {
                $rta_str = 'OBSERVADO';
                return new State(self::COLOR_AMARILLO, $rta_str);
            }
        }
    }

    public static function checkAptoNoApto($tipo_fase, $horas_dormidas_decimal = 0, $horas_fase = 0, $exists_data = 1)
    {
        $rta_str = '';
        if ($exists_data) {
            $horas      = floatval($horas_dormidas_decimal);
            $horas_fase = self::horaToDecimal($horas_fase);
            switch ($tipo_fase) {
                case Dream::FASE_LIGERO:
                    $rta_str = 'FASE_LIGERO';
                    break;
                case Dream::FASE_PROFUNDO:
                    if ($horas_fase >= $horas * 0.1) {
                        $rta_str = 'APTO';
                    } else if ($horas_fase < $horas * 0.07) {
                        $rta_str = 'NO APTO';
                    } else {
                        $rta_str = 'OBSERVADO';
                    }
                    break;

                case Dream::FASE_REM:
                    if ($horas_fase >= $horas * 0.2) {
                        $rta_str = 'APTO';
                    } else if ($horas_fase < $horas * 0.15) {
                        $rta_str = 'NO APTO';
                    } else {
                        $rta_str = 'OBSERVADO';
                    }
                    break;
                default:

            }
        }
        return $rta_str;
    }

    public static function calificacionGeneralTxtReporte($horas_dormidas_decimal, $rem, $profundo, $exists_data)
    {
        $rta_str = '';
        if ($exists_data) {
            /**
             *$horas_dormidas ->100%
             * calificacion: APTO, NO APTO, OBSERVADO
             */
            $horas    = floatval($horas_dormidas_decimal);
            $rem      = self::horaToDecimal($rem);
            $profundo = self::horaToDecimal($profundo);
            if ($horas >= 7 && $rem >= $horas * 0.2 && $profundo >= $horas * 0.1) {
                $rta_str = 'APTO';
            } else if ($horas < 6 && $rem < $horas * 0.15 && $profundo < $horas * 0.07) {
                $rta_str = 'NO APTO';
            } else {
                $rta_str = 'OBSERVADO';
            }
        }
        return $rta_str;
    }

    //TODO: 04:18:00 -> 14 h 18 min
    public static function timeCustom($time, $Addsegundo = false)
    {
        if($time!='0'){
            $time_f = explode(":", $time);
            /*$time_f[0];//hora
            $time_f[1];//min
            $time_f[2];//seg*/

            if(!empty($time_f[2])){
                if ($Addsegundo) {
                    return intval($time_f[0]) . ' h ' . intval($time_f[1]) . ' min ' . intval($time_f[2]) . ' seg';
                } else {
                    return intval($time_f[0]) . ' h ' . intval($time_f[1]) . ' min ';
                }
            }else{
                return intval($time_f[0]) . ' h ' . intval($time_f[1]) . ' min ';
            }

        }else{
            return 0;
        }
    }

    /**
     * @param $time
     * @return float|int|string
     */
    //TODO: 08:30:00 -> 8.5 | return double
    static function horaToDecimal($time)
    {
        $hms = explode(":", $time);
        if (!empty($hms[2])) {
            return ($hms[0] + ($hms[1] / 60) + ($hms[2] / 3600));
        } else {
            return ($hms[0] + ($hms[1] / 60));
        }
    }

    /**
     * Values
     * APTO         : 1
     * OBSERVADO    : 2
     * NO APTO      : 3
     * Que se muestre el MAYOR = PEOR
     */
    public static function converterCalificacionTextNumero($calificacionText)
    {
        $calificacionNum = 0;
        if (!empty($calificacionText)) {
            switch ($calificacionText) {
                case 'APTO':
                    $calificacionNum = 1;
                    break;
                case 'OBSERVADO':
                    $calificacionNum = 2;
                    break;
                case 'NO APTO':
                    $calificacionNum = 3;
                    break;
                default:
                    $calificacionNum = 0;
            }
        }
        return $calificacionNum;
    }

    public static function converterCalificacionNumeroText($calificacionNum, $onlyTxt=false)
    {
        if($onlyTxt){
            switch ($calificacionNum) {
                case 1:
                    return 'APTO';
                case 2:
                    return 'OBSERVADO';
                case 3:
                    return 'NO APTO';
                default:
                    return 'Sin calificación';
            }
        }else{
            switch ($calificacionNum) {
                case 1:
                    $calificacionText = 'APTO';
                    return new State(self::COLOR_VERDE, $calificacionText);
                    break;
                case 2:
                    $calificacionText = 'OBSERVADO';
                    return new State(self::COLOR_AMARILLO, $calificacionText);
                    break;
                case 3:
                    $calificacionText = 'NO APTO';
                    return new State(self::COLOR_ROJO, $calificacionText);
                    break;
                default:
                    return new State(self::COLOR_ROJO, 'Sin calificación');
            }
        }

    }

    public function calidadSt()
    {
        if ($this->calidad <= 30) {
            return new State('#D2554B', $this->calidad);
        } elseif ($this->calidad <= 60) {
            return new State('#A27C55', $this->calidad, 'Se ha reconocido ' . $this->calidad . ' min.');
        } else {
            return new State('#FFF84B', 'Desconocido', 'No se ha actualizado la información.');
        }
    }


}