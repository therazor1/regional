<?php namespace Models;

use Inc\Bases\BaseModel;
use Libs\Pixie\QB;

class Empresa extends BaseModel
{
    const TYPE_EMPRESA_TRANSPORTE=1;

    public $id;
    public $id_type_empresa;
    public $rut;
    public $name;
    public $direccion;
    public $ciudad;
    public $telefono;
    public $pic;

    public static function existEmpresa($name, $id = '0')
    {
        /*return true or false*/
        return QB::table('empresas')
            ->where('id', '!=', $id)
            ->where('name', $name)
            ->where('state', '!=', self::_STATE_DELETED)
            ->first();
    }
}
