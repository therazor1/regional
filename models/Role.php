<?php namespace Models;

use Inc\Bases\BaseModel;

class Role extends BaseModel
{
    const ORDER_BY = 'name';

    const ID_ROOT = '1'; # Identificador Root
    const ID_USUARIO_PRINCIPAL = '2';
    const ID_APROBADOR = '3';
    const ID_AREA_PAGOS = '4';
    const ID_USUARIO_MAESTRO = '5';

    const ID_PROVEEDOR_VENTAS = '14';
    const ID_PROVEEDOR_COBRANZAS = '15';

    public $id;
    public $id_module;
    public $id_type_user;
    public $name;
    public $state;

    /**
     * @param $id_type_user
     * @return Role[]
     */
    static function byTypeUser($id_type_user)
    {
        return static::where('id_type_user', $id_type_user)
            ->where('state', static::_STATE_ENABLED)
            ->get();
    }

}
