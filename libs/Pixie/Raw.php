<?php namespace Libs\Pixie;

class Raw
{
    /* @var string */
    protected $value;

    /* @var array */
    protected $bindings;

    public function __construct($value, $bindings = array())
    {
        $this->value = (string)$value;
        $this->bindings = (array)$bindings;
    }

    public function getBindings()
    {
        return $this->bindings;
    }

    public function __toString()
    {
        return (string)$this->value;
    }

    # HELPERS
    static function now()
    {
        return new Raw('NOW()');
    }

    static function curdate()
    {
        return new Raw('CURDATE()');
    }

    static function distance($lat, $lng, $as = 'distance')
    {
        return new Raw("SQRT(POW(69.1 * (lat - $lat), 2) + POW(69.1 * ($lng - lng) * COS(lat / 57.3), 2)) * 1.609344 $as");
    }

    static function dateAdd($num, $unit = 'SECOND', $field = 'CURRENT_TIMESTAMP')
    {
        return new Raw("DATE_ADD($field, INTERVAL $num $unit)");
    }

    static function count($col, $as = 'total')
    {
        return new Raw("COUNT($col) $as");
    }

    static function coalesceZero($val, $alias = null)
    {
        return new Raw("COALESCE($val,0)" . ($alias ? ' as ' . $alias : ''));
    }
}
