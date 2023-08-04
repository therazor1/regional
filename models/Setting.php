<?php namespace Models;

use Inc\Bases\BaseModel;

class Setting extends BaseModel
{

    const ID = 'name';
    const STATE = null;

    public $name;
    public $value;
    public $description;

}