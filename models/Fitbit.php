<?php namespace Models;

use DateTime;
use Inc\database\QB;
use Inc\Util;
class Fitbit {

    public static $idFitbit = null;
    public $clientId = "23QYJM";


    public static function init($idFitbit = ""){
        $qb = QB::table('fitbit');
        $qb->select('token', 'refresh_token');
        $qb->where('id_fitbit', $idFitbit);
        $data = $qb->get();
        return $data[0];
    }


    public static function getInfoDreams($idFitbit = "", $sevendays = false, $limite=100, $sync = false){
        $getInfo = self::init($idFitbit);
        $token = $getInfo->token;
        $refreshToken = $getInfo->refresh_token;
        $response = self::curlInfo($idFitbit, $token, $sevendays, $limite, $sync);
        if($response){
            return $response;
        }else{
            //Refresh Token
            $response = self::refreshToken($refreshToken, $idFitbit);
            if($response){
                $send = self::curlInfo($idFitbit, $token, $sevendays, $limite, $sync);
                if($send !== []){
                    return $send['sleep'];
                }else{
                    return [];
                }
            }
        }
    }

    public static function curlInfo($idFitbit, $token, $sevenDays, $limite, $sync){
        $url = "";
        // $hoy = Util::today();
        $hoy = "2023-07-19";
        $ago = Util::agoSevenDays();
        if($sync){
            $url = "https://api.fitbit.com/1.2/user/{$idFitbit}/sleep/date/{$hoy}.json";
        }else{
            if($sevenDays){
                $url = "https://api.fitbit.com/1.2/user/{$idFitbit}/sleep/date/{$ago}/{$hoy}.json";
            }else{
                $url = "https://api.fitbit.com/1.2/user/{$idFitbit}/sleep/list.json?afterDate=2020-05-01&sort=desc&offset=0&limit={$limite}";
            }
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$token}",
                'Cookie: JSESSIONID=185C73730E75608EEA0B3A5B78E8E688.fitbit1; fct=0c99739271cb4b8aa4653699c9eeb236'
            ),
        ));
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return self::formatedJson($response, $sync);
    }

    public static function refreshToken($refreshToken, $idFitbit){
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fitbit.com/oauth2/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => "grant_type=refresh_token&client_id=23QYJM&refresh_token={$refreshToken}",
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Cookie: JSESSIONID=185C73730E75608EEA0B3A5B78E8E688.fitbit1; fct=0c99739271cb4b8aa4653699c9eeb236'
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        if($refreshToken !== ""){
            QB::table("fitbit")
                 ->where('id_fitbit', $idFitbit)
                 ->update([
                    'token' => $response['access_token'],
                    'refresh_token' => $response['refresh_token']
                 ]);
            return true;
        }else{
            echo json_encode("No valido");
            return false;
            exit;
        }
    }


    public static function formatedJson($json, $sync){
        $data = [];
        if($sync){
            foreach($json['sleep'] as $info){
                $data[] = array(
                    'inicio_dormir' => Util::otherDateTime($info['startTime']),
                    'fin_dormir' => Util::otherDateTime($info['endTime']),
                    'horas_dormidas' => Util::timeFormatMilisecconds($info['duration'], false),
                    'horas_dormidas_decimal' => Util::horaDecimal(Util::timeFormatMilisecconds($info['duration'], false)),
                    'ligero' => Util::minutosAHorasMinutos($info['levels']['summary']['light']['minutes']),
                    'ligero_porc' => ceil(($info['levels']['summary']['light']['minutes']/ Util::timeFormatMilisecoondsToMinutes($info['duration']) * 100)),
                    'profundo' => Util::minutosAHorasMinutos($info['levels']['summary']['deep']['minutes']),
                    'produndo_proc' =>ceil(($info['levels']['summary']['deep']['minutes']/ Util::timeFormatMilisecoondsToMinutes($info['duration']) * 100)),
                    'rem' => Util::minutosAHorasMinutos($info['levels']['summary']['rem']['minutes']),
                    "rem_porc" => ceil(($info['levels']['summary']['rem']['minutes']/ Util::timeFormatMilisecoondsToMinutes($info['duration']) * 100)),
                    'horas_despierto' => Util::minutosAHorasMinutos($info['levels']['summary']['wake']['minutes']),
                    'calidad' => $info['efficiency'],
                    'calificacion' => "1",
                    "observacion" => "Ok",
                    "sincronizado_hoy" => "1",
                    "exists_data" => "1",
                    'calificacion_general' => Dream::getPeorCalificacionGeneralTxtDinamico(Util::horaDecimal(Util::timeFormatMilisecconds($info['duration'], false)), Util::minutosAHorasMinutos($info['levels']['summary']['deep']['minutes']), Util::minutosAHorasMinutos($info['levels']['summary']['rem']['minutes']), true, true)
                );
            }
        }else{
            foreach($json['sleep'] as $info){
    
                $data[] = array(
                    'dateOfSleep' => Util::verboseDate($info['dateOfSleep']),
                    'compareDate' => Util::compareDate($info['dateOfSleep']),
                    'duration' => Util::timeFormatMilisecconds($info['duration']),
                    'calificacion_horas_dormidas' => Dream::getCalificacionHorasDormidasObj(Util::milisegundosToHMS($info['duration']), true, true),
                    'efficiency' => $info['efficiency'],
                    'endTime' => $info['endTime'],
                    'startTime' => $info['startTime'],
                    'infoCode' => $info['infoCode'],
                    'isMainSleep' => $info['isMainSleep'],
                    'asleep' => (!empty($info['levels']['summary']['asleep']['minutes'])) ? Util::timeFormatHM($info['levels']['summary']['asleep']['minutes']) : "0", //Dormido
                    'asleep_count' => $info['levels']['summary']['asleep']['count'] ?? 0, //Cantidad dormido
                    'awake' => (!empty($info['minutesAwake'])) ? Util::timeFormatHM($info['minutesAwake']) : "0",  //Despierto
                    'restless' => (!empty($info['levels']['summary']['restless']['minutes'])) ? Util::timeFormatHM($info['levels']['summary']['restless']['minutes']) : "0", // Inquieto
                    'restless_count' => $info['levels']['summary']['restless']['count'] ?? 0,
                    'deep' => (!empty($info['levels']['summary']['deep']['minutes'])) ? Util::timeFormatHM($info['levels']['summary']['deep']['minutes']) : "0", // Profundo
                    'deepPercent' => (!empty($info['levels']['summary']['deep']['minutes'])) ? ceil(($info['levels']['summary']['deep']['minutes']/ Util::timeFormatMilisecoondsToMinutes($info['duration']) * 100))."%" : "0", // Percent Profundo 
                    'deep_calification' => (!empty($info['levels']['summary']['deep']['minutes'])) ? Dream::calificacionProfundo(Util::milisegundosToHMS($info['duration']), Util::minutosToHMS($info['levels']['summary']['deep']['minutes']), true)->name : "NO APTO",
                    'deep_count' => $info['levels']['summary']['deep']['count'] ?? 0,
                    'light' => (!empty($info['levels']['summary']['light']['minutes'])) ? Util::timeFormatHM($info['levels']['summary']['light']['minutes']) : "0", // Ligero
                    'lightPercent' => (!empty($info['levels']['summary']['light']['minutes'])) ? ceil(($info['levels']['summary']['light']['minutes']/ Util::timeFormatMilisecoondsToMinutes($info['duration']) * 100))."%" : "0", // Percent Ligero
                    'light_count' => $info['levels']['summary']['light']['count'] ?? 0,
                    'rem' => (!empty($info['levels']['summary']['rem']['minutes'])) ? Util::timeFormatHM($info['levels']['summary']['rem']['minutes']) : "0", // Rem
                    'remPercent' => (!empty($info['levels']['summary']['rem']['minutes'])) ? ceil(($info['levels']['summary']['rem']['minutes']/ Util::timeFormatMilisecoondsToMinutes($info['duration']) * 100))."%" : "0", // Percent Rem
                    'rem_count' => $info['levels']['summary']['rem']['count'] ?? 0,
                    'rem_calification' => (!empty($info['levels']['summary']['rem']['minutes'])) ? Dream::calificacionREM(Util::milisegundosToHMS($info['duration']), Util::minutosToHMS($info['levels']['summary']['rem']['minutes']), true)->name : "NO APTO", // Rem
                    'wake' => (!empty($info['levels']['summary']['wake']['minutes'])) ? Util::timeFormatHM($info['levels']['summary']['wake']['minutes']) : "0", // Despertar
                    'wake_count' => $info['levels']['summary']['wake']['count'] ?? 0,
                    'calificacion_peor' => (!empty($info['levels']['summary']['deep']['minutes'])) ? Dream::getPeorCalificacionGeneralTxtDinamico(Util::milisegundosToHMS($info['duration']), Util::minutosToHMS($info['levels']['summary']['deep']['minutes']), Util::minutosToHMS($info['levels']['summary']['rem']['minutes']), true , true) : "NO APTO",
                );
            }
        }
        return $data;
    }


    public static function validarIdFitbit($idFitbit){
        if($idFitbit == ""){
            return true;    
        }else{
            $patron = '/^[A-Z0-9]{6}$/';
            return preg_match($patron, $idFitbit);
        }
    }


}


?>