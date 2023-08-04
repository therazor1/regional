<?php namespace Models;

class Par
{
    public $id;
    public $nombre;
    public $tipo;
    public $datos;

    public function __construct($id, $nombre)
    {
        $this->id = $id;
        $this->nombre = $nombre;
    }

    public function tipo($tipo)
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function datos($datos)
    {
        $this->datos = $datos;
        return $this;
    }
}