<?php namespace Inc\database;

use PDO;

class QB
{

    public static function table($tables)
    {
        if (!is_array($tables)) {
            $tables = func_get_args();
        }
        return QBuilder::ins()->table($tables);
    }

    public static function query($value, $bindings = array())
    {
        return QBuilder::ins()->query($value, $bindings);
    }

    public static function date()
    {
        return date('Y-m-d H:i:s');
    }

    public static function pdo()
    {
        return QBuilder::ins()->pdo();
    }

    /**
     * @return PDO
     * @deprecated
     */
    public static function cn()
    {
        return QBuilder::ins()->pdo();
    }

}