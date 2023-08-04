<?php namespace Models;

use Inc\Bases\BaseModel;

class Module extends BaseModel {

    const DATE_CREATED = null;
    const DATE_UPDATED = null;
    const DATE_DELETED = null;

    public $id;
    public $id_parent;
    public $sort;
    public $name;
    public $url;
    public $icon;
    public $root;
    public $state;

}