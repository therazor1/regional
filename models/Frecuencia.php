<?php namespace Models;

use Inc\Bases\BaseModel;

class Frecuencia extends BaseModel
{
    protected $table='frecuencias';

    public $id;
    public $id_cliente;
    public $escolta;
    public $frecuencia;
    public $veces;
}