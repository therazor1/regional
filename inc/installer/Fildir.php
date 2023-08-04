<?php namespace Inc\installer;

class Fildir
{
    const TYPE_DIR = 'dir';
    const TYPE_FILE = 'file';

    public $type;
    public $name;
    public $path;
    public $size;
    public $items;

    /**
     * Fildir constructor.
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }


}