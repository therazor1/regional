<?php namespace Controllers\test;

use Inc\installer\Installer;

class T_Installer extends _controller
{

    public function verificar()
    {
        return Installer::ins()->verificar();
    }

    public function leerCrons()
    {
        return Installer::ins()->leerCrons();
    }

    public function crearCopiasDeSeguridad()
    {
        return Installer::ins()->crearCopiasDeSeguridad();
    }

    public function leerCopiasDeSeguridad()
    {
        return Installer::ins()->leerCopiasDeSeguridad();
    }

}