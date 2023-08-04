<?php namespace Inc\database;

use Libs\SqlFormatter;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

class QBuilder extends QueryBuilderHandler
{

    /** @var QBuilder */
    private static $ins;

    public static function ins()
    {

        if (!self::$ins) {

            $config = array(
                'driver'    => 'mysql', // Db driver
                'host'      => DB_HOST,
                'database'  => DB_NAME,
                'username'  => DB_USER,
                'password'  => DB_PASS,
                'charset'   => 'utf8', // Optional
                'collation' => 'utf8_unicode_ci', // Optional
                'options'   => [ // PDO constructor options, optional
                    #PDO::ATTR_TIMEOUT          => 5,
                    #PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );

            $connection = new Connection('mysql', $config);

            self::$ins = new QBuilder($connection);
        }

        return self::$ins;
    }

    public function getSQL()
    {
        return $this->getQuery()->getRawSql();
    }

    public function whereLike($key, $value)
    {
        return $this->where($key, 'LIKE', '%' . str_replace(' ', '%', $value) . '%');
    }

    public function showSQL($exit = true)
    {
        $sql = '<hr>' . SqlFormatter::format($this->getSQL());

        if ($exit)
            exit($sql);
        else
            echo $sql;
    }

}