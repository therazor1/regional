<?php namespace Inc\database;

class Raw extends \Pixie\QueryBuilder\Raw
{

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

    static function dateAdd($num, $unit = 'SECOND', $field = 'NOW()')
    {
        return new Raw("DATE_ADD($field, INTERVAL $num $unit)");
    }

    static function count($col, $as = 'total')
    {
        return new Raw("COUNT($col) $as");
    }

}