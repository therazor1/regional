<?php namespace Inc;

use DateTime;

class Date
{
    static $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    static $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
        'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    static $months_short = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SET', 'OCT', 'NOV', 'DIC'];


    public $timestamp;

    protected $_parts;

    public function __construct($timestamp = null)
    {
        $this->timestamp = $timestamp
            ? is_numeric($timestamp)
                ? $timestamp
                : strtotime($timestamp)
            : time();
    }

    public function humanDate()
    {
        return date('d/m/Y', $this->timestamp);
    }

    public function humanTime()
    {
        return date('h:i A', $this->timestamp);
    }

    public function humanDatetime()
    {
        return date('d/m/Y H:m', $this->timestamp);
    }

    public function verboseDatetime($onlyDate = false)
    {
        $dayWeek = $this->sDayWeek(); # dia de la semana
        $dayMonth = $this->part('mday'); # dia del mes sin ceros
        $month = $this->sMonth();
        $year = $this->part('year');

        $result = $dayWeek . ', ' . $dayMonth . ' de ' . strtolower($month) . ' de ' . $year;

        if (!$onlyDate) {
            $result .= ' a las ' . $this->humanTime();
        }

        return $result;
    }

    public function verboseDate()
    {
        return $this->verboseDatetime(true);
    }

    public function sHour()
    {
        $hour = $this->part('hours');

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

    public function sDayWeek()
    {
        $dayWeek = $this->part('wday');
        return isset(self::$days[$dayWeek]) ? self::$days[$dayWeek] : '';
    }

    public function sMonth()
    {
        $month = $this->part('mon') - 1;
        return isset(self::$months[$month]) ? self::$months[$month] : '';
    }


    public function ago($short = false)
    {
        $periods = $short
            ? ['seg', 'min', 'hor', 'día', 'sem', 'mes', 'año', 'dec']
            : ['seg', 'min', 'hora', 'día', 'sem', 'mes', 'año', 'dec'];
        $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];

        $difference = $this->timestamp - time();
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
            if ($lengths[$j] == '12')
                $periods[$j] .= 'e';
            $periods[$j] .= 's';
        }

        return $prefx . ' ' . $difference . ' ' . $periods[$j];
    }

    public function age()
    {
        $dob = new DateTime();
        $dob->setTimestamp($this->timestamp);
        $now = new DateTime();
        $difference = $now->diff($dob);
        return $difference->y;
    }

    public function sAge()
    {
        if ($age = $this->age()) {
            return $age . ' años';
        } else {
            return '';
        }
    }

    public function year()
    {
        return $this->part('year');
    }

    public function format($format = 'Y-m-d H:i:s')
    {
        return date($format, $this->timestamp);
    }

    function parts()
    {
        if (!$this->_parts)
            $this->_parts = getdate($this->timestamp);
        return $this->_parts;
    }

    function part($part)
    {
        return $this->parts()[$part];
    }

    # HELPERS

    public static function ins($time = null)
    {
        return new static($time);
    }

    public function __toString()
    {
        return $this->format();
    }


}