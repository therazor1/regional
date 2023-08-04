<?php namespace Models;

use Inc\Bases\BaseModel;

class Operacion extends BaseModel
{
    protected $table='operaciones';

    public $id=0;
    public $descripcion;
    public $id_conductor;
    public $id_emp_transporte;
    public $id_supervisor;
    public $email_supervisor;
    public $id_cliente;
    public $id_zona;
}
