<?php namespace Models;

use Inc\Bases\BaseModel;

class Personal extends BaseModel
{
    public $id = 0;
    public $id_type_user;
    public $id_role;
    public $id_client;
    public $id_emp_transporte;

    public $name;
    public $surname;
    public $email;
    public $phone;
    public $document;
    protected $password; // Protegido, no se imprime
    protected $id_dias_trabajo;
    protected $id_tipo_salida;
    protected $state_laboral;
    public $escolta;
    public $pic;
    public $date_created;
}
