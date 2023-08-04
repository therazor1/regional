<?php namespace Models;

use Inc\Bases\BaseModel;

class Session extends BaseModel
{

    public $id;
    public $id_user;
    public $uuid;
    public $token;
    public $state;
    public $date_expiration;
    public $date_created;

    public static function get($uuid, $id_user)
    {
        return parent::queryFirst("
            SELECT *
            FROM sessions
            WHERE uuid = '$uuid' AND id_user = '$id_user'
        ");
    }

    public static function debugUUID()
    {
        return 'TSTUUIDDEBUG00112233';
    }

}