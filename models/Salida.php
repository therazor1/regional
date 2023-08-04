<?php namespace Models;

use Inc\Bases\BaseModel;

class Salida extends BaseModel
{
    protected $table='salidas';

    public $id;
    public $id_conductor;
    public $id_cliente;
    public $hora_inicio;
    public $escolta;
}