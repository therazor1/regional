<?php namespace Controllers\api;

class legal extends _controller
{

    public function index()
    {
        return file_get_contents(_PATH_ . '/legal.html');
    }

}