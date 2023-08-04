<?php namespace Inc;

use DateTime;
use Libs\Pixie\QB;
use stdClass;

class Util
{

    public static function done(Rsp $rsp)
    {
        header('Content-Type: application/json');
        exit(json_encode($rsp));
    }

    public static function done_rsp($text = null)
    {
        header('Content-Type: application/json');
        exit(json_encode(rsp($text)));
    }

    public static function done_view($text = null)
    {
        exit(view('e404')->set('text', $text));
    }

    public static function redirect($uri)
    {
        header('Location: ' . URL_WEB . $uri);
        exit;
    }

    // Calcular edad desde una fecha
    public static function calcAge($date)
    {
        $dob = new DateTime($date);
        $now = new DateTime();
        $difference = $now->diff($dob);
        $age = $difference->y;
        return $age;
    }

    // Calcular edad desde una fecha
    public static function calcAgeStr($date)
    {
        if ($age = self::calcAge($date)) {
            return $age . ' años';
        } else {
            return 'no definido';
        }
    }

    // Metros a Kilometros
    public static function parseMeters($m)
    {
        return round($m / 1000, 1) . ' KM';
    }

    // Convertir segundos a x tiempo
    public static function parseDuration($seconds)
    {
        $minutes = floor($seconds / 60);
        return sprintf("%02d", $minutes) . ' MIN';
    }

    // Generar codigo de verificacion
    public static function rand($digits = 4)
    {
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }

    // Generar token de seguridad unico
    public static function token($identifiers = '')
    {
        return md5(uniqid($identifiers));
    }

    /**
     * Generar un codigo aleatorio unico temporal
     * @param int $digits
     * @param bool $numeric
     * @return string
     */
    public static function code($digits = 6, $numeric = false)
    {
        if ($numeric) {
            $code = '';
            $count = 0;
            while ($count < $digits) {
                $random_digit = mt_rand(0, 9);

                $code .= $random_digit;
                $count++;
            }

            return $code;
        } else {
            $code = strtoupper(substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $digits));
            return $code;
        }
    }

    // Generar clave aleatoria
    public static function password($length = 8)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%&*?';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    // Saber si es un numero de telefono
    public static function isPhone($number)
    {
        return is_numeric($number) && strlen($number) == 9;
    }

    // Saber si es un email
    public static function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Saber si es un dni
    public static function isDNI($dni)
    {
        return is_numeric($dni) && strlen($dni) == 8;
    }

    /**
     * Validar si una fecha es correcta o esta en formato correcto: 2017-12-24
     * @param $str_date
     * @return bool
     */
    public static function isDate($str_date)
    {
        $arr = explode('-', $str_date);
        if (count($arr) == 3) {
            if (is_numeric($arr[0]) && $arr[0] > 1000) {
                if (is_numeric($arr[1]) && $arr[1] > 0 && $arr[1] <= 12) {
                    if (is_numeric($arr[2]) && $arr[2] > 0 && $arr[2] <= 31) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function isDateTime($date_time_str)
    {
        return (DateTime::createFromFormat('Y-m-d H:i', $date_time_str) !== false) ||
            (DateTime::createFromFormat('Y-m-d H:i:s', $date_time_str) !== false) || 
            (DateTime::createFromFormat('Y-m-d H:i:s.u', $date_time_str) !== false);
    }

    public static function otherDateTime($date_time_str){
        $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s.u', $date_time_str);

        if ($datetime !== false) {
            return $datetime->format('Y-m-d H:i:s');
        } else {
            return "Error al crear el objeto DateTime";
        }
    }

    // Calcular distancia entre 2 puntos
    // $unit: "M" => miles, "K" => kilometers, "N" => nautical miles
    public static function distance($lat1, $lon1, $lat2, $lon2, $unit = 'K')
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2))
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    /**
     * @param string $date
     * @return false|int|string
     */
    public static function getYears($date)
    {
        $birthDate = explode("-", $date);
        if (is_array($birthDate) && count($birthDate) == 3) {
            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md")
                ? ((date("Y") - $birthDate[0]) - 1)
                : (date("Y") - $birthDate[0]));
        } else {
            $age = 0;
        }
        return $age;
    }

    /**
     * Obtener direccion mediante coordenadas
     * @param float $lat
     * @param float $lng
     * @return string
     */
    public static function getAddress($lat, $lng)
    {
        $rsp = @file_get_contents('https://www.uber.com/api/address-lookup?lat=' . $lat . '&lng=' . $lng);
        if ($rsp) {
            $json = @json_decode($rsp);
            if ($json && $json->longAddress) {
                return $json->longAddress;
            }
        }
        return '';
    }

    /**
     * Obtener "hace x tiempo" mediante segundos
     * @param $dateOrTime
     * @param bool $short
     * @return string
     */
    public static function ago($dateOrTime, $short = false)
    {
        $periods = $short
            ? ['seg', 'min', 'hor', 'día', 'sem', 'mes', 'año', 'dec']
            : ['seg', 'min', 'hora', 'día', 'sem', 'mes', 'año', 'dec'];
        $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];

        if (is_numeric($dateOrTime)) {
            $time = $dateOrTime;
        } else {
            $time = strtotime($dateOrTime);
        }

        $difference = $time - time();
        if ($short) {
            $prefx = $difference < 0 ? '-' : '+';
        } else {
            $prefx = $difference < 0 ? 'Hace' : 'En';
        }
        $difference = abs($difference);

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1 && !$short) {
            $periods[$j] .= "s";
        }

        return $prefx . ' ' . $difference . ' ' . $periods[$j];
    }

    /**
     * Ordenar menu/categoria multinivel
     * @param $array
     * @param int $parent_id
     * @return array
     */
    public static function ordMenu($array, $parent_id = 0)
    {
        $temp_array = array();
        foreach ($array as $element) {
            if ($element->id_parent == $parent_id) {
                $element->children = Util::ordMenu($array, $element->id);
                if ($element->children) {
                    $element->type = 'collapse';
                }
                $temp_array[] = $element;
            }
        }
        return $temp_array;
    }

    /**
     * Obtener el numero de semanas entre 2 fechas
     * @param $date1
     * @param $date2
     * @return float
     */
    public static function datediffInWeeks($date1, $date2)
    {
        if ($date1 > $date2) return Util::datediffInWeeks($date2, $date1);
        $first = DateTime::createFromFormat('Y-m-d', $date1);
        $second = DateTime::createFromFormat('Y-m-d', $date2);
        return floor($first->diff($second)->days / 7);
    }

    /**
     * Hacer una peticion a url
     * @param $url
     * @param array $data
     * @return false|string
     */
    public static function callUrl($url, $data = [])
    {
        $url = sprintf("%s?%s", $url, http_build_query($data));
        $result = @file_get_contents($url);
        return $result;
    }

    /**
     * Hacer una llamada api
     * @param $url
     * @param array $data
     * @return mixed
     */
    public static function callAPI($url, $data = [])
    {
        $result = self::callUrl($url, $data);
        return json_decode($result);
    }


    public static function api($url, $method, $data = [], $headers = [])
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json',
        ], $headers));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if (!$result) {
            return null;
        }
        curl_close($curl);
        return json_decode($result);
    }

    public static function apiPost($url, $data = [], $headers = [])
    {
        return self::api($url, 'POST', $data, $headers);
    }

    public static function apiGet($url, $data = [], $headers = [])
    {
        return self::api($url, 'GET', $data, $headers);
    }

    /**
     * Recibe el monto,  y retorna formateado, ej.: S/ 99.00
     * @param $amount
     * @return string
     */
    public static function coin($amount)
    {
        return STG::get('coin') . ' ' . number_format($amount, 2, '.', ',');
    }

    /**
     * Armar un código de documento, con ceros a la izquierda: 000003
     * @param $num
     * @param int $length
     * @return string
     */
    public static function doc($num, $length = 8)
    {
        return str_pad($num, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Implode natural
     * @param $array
     * @param string $glue
     * @param string $lastGlue
     * @return string : xx, yy y zz
     */
    public static function naturalImplode($array, $glue = ', ', $lastGlue = ' y ')
    {
        return join($lastGlue, array_filter(
            array_merge([join($glue, array_slice($array, 0, -1))], array_slice($array, -1)), 'strlen'));
    }

    public static function humanDatetime($datetime)
    {
        return $datetime ? date('d/m/Y h:i A', strtotime($datetime)) : '';
    }

    public static function humanTime($datetime)
    {
        return $datetime ? date('h:i A', strtotime($datetime)) : '';
    }

    public static function humanDate($date)
    {
        return $date && $date != '0000-00-00' ? date('d/m/Y', strtotime($date)) : '';
    }

    public static function timeFormat($minutes)
    {
        return sprintf('%01d:%02d', floor($minutes / 60), floor($minutes % 60));
    }

    public static function timeFormatHM($minutes, $formatText = true)
    {
        return sprintf('%01dh %02dmin', floor($minutes / 60), floor($minutes % 60));
    }

    public static function minutosAHorasMinutos($minutos) {
        $horas = floor($minutos / 60);
        $minutosRestantes = $minutos % 60;
    
        // Agregar 0 adelante de las horas si son menores a 10
        $horas = ($horas < 10) ? '0' . $horas : $horas;
    
        return sprintf('%s:%02d:00', $horas, $minutosRestantes);
    }
    

    public static function timeFormatMilisecconds($milisecconds, $formatText = true){
        $minutos = $milisecconds / 1000 / 60;
        $horas = floor($minutos / 60);
        $minutosRestantes = floor($minutos % 60);
        if($formatText){
            return sprintf('%01dh %02dmin', $horas, $minutosRestantes);
        }else{
            $horasMinutos = sprintf('%01dh %02dmin', $horas, $minutosRestantes);
            list($horas, $minutos) = sscanf($horasMinutos, '%dh %dmin');
            $horas = ($horas < 10) ? '0' . $horas : $horas;
            $segundos = '00';
            return sprintf('%s:%02d:%s', $horas, $minutos, $segundos);
        }
    }

    public static function horaDecimal($hora) {
        list($horas, $minutos) = explode(':', $hora);
        $valorDecimal = (int)$horas + ((int)$minutos / 60);
        return round($valorDecimal, 2);
    }
    

    public static function milisegundosToHMS($milisecconds){
        $segundos = floor($milisecconds / 1000);
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segundos = $segundos % 60;
        return sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos); 
    }

    public static function minutosToHMS($minutos) {
        $horas = floor($minutos / 60);
        $minutos = $minutos % 60;
        $segundos = 0;
        return sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
    }

    public static function compareDate($fecha){
        $fechaHoy = date('Y-m-d');
        return ($fecha == $fechaHoy);
    }

    public static function timeFormatMilisecoondsToMinutes($milisecconds){
        $minutos = $milisecconds / 60000;
        return $minutos;
    }

    public static function strHour($hour)
    {
        if ($hour <= 6) {
            return 'madrugada';
        } else if ($hour <= 12) {
            return 'mañana';
        } else if ($hour <= 18) {
            return 'tarde';
        } else {
            return 'noche';
        }
    }

    public static function strDayWeek($dayWeek)
    {
        return isset(Date::$days[$dayWeek]) ? Date::$days[$dayWeek] : '';
    }

    public static function verboseDatetime($datetime, $onlyDate = false)
    {
        $time = is_numeric($datetime) ? $datetime : strtotime($datetime);

        $dayWeek = date('w', $time); # dia de la semana
        $dayMonth = date('j', $time); # dia del mes sin ceros
        $month = date('n', $time) - 1;
        $year = date('Y', $time);

        $result = Date::$days[$dayWeek]
            . ', '
            . $dayMonth
            . ' de '
            . strtolower(Date::$months[$month])
            . ' de '
            . $year;

        if (!$onlyDate) {
            $result .= ' a las ' . date('h:i A', $time);
        }

        return $result;
    }

    public static function verboseDate($date)
    {
        return self::verboseDatetime($date, true);
    }

    public static function today(){
        return date("Y-m-d");
    }

    public static function agoSevenDays(){
        $fechaActual = date("Y-m-d"); // Fecha actual
        return date("Y-m-d", strtotime("-7 days", strtotime($fechaActual))); 
    }

    public static function htmlSummary($content, $max = 50)
    {
        $content = strip_tags($content);
        $content = trim($content);
        $content = html_entity_decode($content);
        $content = strlen($content) > $max ? substr($content, 0, $max) . '...' : $content;
        return $content;
    }

    static function humanFilesize($size, $precision = 2): string
    {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision) . $units[$i];
    }

    /**
     * @param $items
     * @param $item
     * @param string $key
     * @return \stdClass
     */
    public static function arraySearchByObjectKey($items, $item, $key = 'id')
    {
        $obj = new \stdClass();
        $obj->ok = false;
        $obj->index = null;
        $obj->item = null;
        $obj->items = [];

        $objects = [];

        foreach (array_filter(
                     $items,
                     function ($e) use (&$item, $key) {
                         if (is_object($item)) {
                             return $e->$key == $item->$key;
                         } else if (is_array($item)) {
                             return $e->$key == $item[$key];
                         } else {
                             return $e->$key == $item;
                         }
                     }
                 ) as $index => $_obj) {
            $obj_2 = Util::merge($_obj);
            $obj_2->index = $index;
            $objects[] = $obj_2;
        }

        if (count($objects) > 0) $obj->ok = true;
        $obj->index = @$objects[0] ? $objects[0]->index : '';
        $obj->item = @$objects[0] ?: '';
        $obj->items = $objects;

        return $obj;
    }

    public static function merge($obj_or_arr)
    {
        $obj = new \stdClass();
        foreach ($obj_or_arr as $k => $v) {
            $obj->$k = $v;
        }

        return $obj;
    }

    public static function getArrValue($_items = [], $_key = 'id', $num = true)
    {
        $items = [];

        foreach ($_items as $item) {
            $_v = @$item->$_key ? $item->$_key : $item[$_key];
            $items[] = $num ? (int)$_v : $_v;
        }

        return $items;
    }

    static function osArr($os = null)
    {
        $items = [];
        foreach ($os as $id => $o) {
            $o->id = $id;
            $items[] = $o;
        }
        return $items;
    }

}
