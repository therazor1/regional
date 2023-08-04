<?php namespace Controllers\admin;

class general
{
    public function legal()
    {
        return file_get_contents(_PATH_ . '/legal.html');
    }
}