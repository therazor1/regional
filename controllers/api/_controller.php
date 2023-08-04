<?php namespace Controllers\api;
use Inc\Auth;
use Inc\Route;
use Inc\Util;

class _controller
{
    var $not_auth_metods = ['test'];

    public function __construct($auth_required = true)
    {
        if ($auth_required && !in_array(Route::$method, $this->not_auth_metods)) {

              if (Auth::init()) {

              } else {
                  Util::done_rsp('not_logged');
              }
        }
    }

}