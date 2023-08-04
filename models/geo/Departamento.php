<?php namespace Models\geo;

use Inc\Bases\BaseModelES;

class Departamento extends BaseModelES
{
    const ORDER_BY = 'nombre';
    const STATE = null;
    const DATE_CREATED = null;
    const DATE_UPDATED = null;
    const DATE_DELETED = null;

    public $id;
    public $nombre;
}