<?php namespace Controllers\admin;

use Inc\Bases\BaseController;
use Inc\Auth;
use Models\Obra;

class _controller extends BaseController
{

    function obra()
    {
        return Obra::find(Auth::id_obra());
    }

}