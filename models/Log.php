<?php namespace Models;

use Inc\Auth;
use Inc\Bases\BaseModel;

class Log extends BaseModel
{

    const LOGIN = 1; // :id_user inicio sesion
    const LOGOUT = 2; // :id_user cerro sesion
    const CREATE = 3; // :id_user creo :id_ref en tabla :target
    const UPDATE = 4; // :id_user actualizo :id_ref en tabla :target
    const DELETE = 5; // :id_user elimino :id_ref en tabla :target
    const ENABLE = 6;
    const DISABLE = 7;

    public $id;
    public $id_type_log;
    public $id_user;
    public $id_target;
    public $target;
    public $data;
    public $date_created;

    static function add($id_type_log, $id_user, $id_target = 0, $target = '', $data = '', $id_parent = 0, $parent = '')
    {
        Log::insert([
            'id_type_log' => $id_type_log,
            'id_user'     => $id_user,
            'id_target'   => $id_target,
            'target'      => $target,
            'data'        => (is_array($data) || is_object($data)) ? json_encode($data) : $data,
            'id_parent'   => $id_parent,
            'parent'      => $parent,
        ]);
    }

    static function addMe($id_type_log, $id_target = 0, $target = '', $data = '', $id_parent = 0, $parent = '')
    {
        self::add($id_type_log, Auth::id(), $id_target, $target, $data, $id_parent, $parent);
    }

}