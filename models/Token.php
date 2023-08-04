<?php namespace Models;

use Inc\Bases\BaseModel;

class Token extends BaseModel
{
    const  TYPE_RECOVER = '1';
    const  TYPE_PROVIDER_INVITATION = '2';

    public $id;
    public $id_user;
    public $type;
    public $token;
    public $state;
    public $date_expiration;

    public static function get($token)
    {
        return self::find('token', '=', $token);
    }

    function hasExpired()
    {
        return $this->date_expiration < date('Y-m-d H:m:s');
    }
}