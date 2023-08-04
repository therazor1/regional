<?php namespace Models;

use Inc\Bases\BaseModel;

class Dias_trabajo extends BaseModel
{
    protected $table='dias_trabajo';

    public $id=0;
    public $lunes;
    public $martes;
    public $miercoles;
    public $jueves;
    public $viernes;
    public $sabado;
    public $domingo;
}
