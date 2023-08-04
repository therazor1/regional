<?php namespace Models;

use Inc\Bases\BaseModel;
use Libs\Pixie\QB;

class Client extends BaseModel
{
    public $id;
    public $name;
    public $document;
    public $address;

    public static function existClient($name, $id = '0')
    {
        /*return true or false*/
        return QB::table('clients')
            ->where('id', '!=', $id)
            ->where('name', $name)
            ->where('state', '!=', self::_STATE_DELETED)
            ->first();
    }
}
