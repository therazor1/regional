<?php namespace Models;

class State
{
    public $id;
    public string $color;
    public string $name;
    public string $surname;

    /**
     * State constructor.
     * @param string $color
     * @param string $name
     * @param string $surname
     */
    public function __construct(string $color = '', string $name = '', string $surname = '')
    {
        $this->color = $color;
        $this->name = $name;
        $this->surname = $surname;
    }

}