<?php namespace Models;

use Inc\Bases\BaseModel;

class TypeUser extends BaseModel
{
    const DATE_CREATED = null;
    const DATE_UPDATED = null;
    const DATE_DELETED = null;
    const STATE = null;

    const ORDER_BY = 'id';

    public $id;
    public $name;
}