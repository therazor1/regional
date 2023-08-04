<?php namespace Models\geo;

use Inc\Bases\BaseModelES;

class Pais extends BaseModelES
{
    protected $table = 'paises';
    const ORDER_BY = 'nombre';
    const STATE = null;

    public $id;
    public $codigo;
    public $nombre;
}