<?php namespace Inc\Bases;

use Inc\Auth;
use Inc\database\QB;
use Inc\database\Raw;
use Inc\Date;
use Inc\Perms;
use Inc\Route;
use Inc\STG;
use Inc\Util;
use Models\Proveedor;

abstract class BaseController
{
    const AUTH_REQUIRED = true; # Autenticación requerida
    const CAN_ALL = false; # si esta en TRUE cualquiera logueado puede ver/editar
    const UPDATE_SESSION_EXPIRATION = true; # actualiza la fecha de expiracion de la sesion, no cuando es automatico
    public $modules = []; # aparte del $module, tmb verifica si tiene acceso a uno de estos

    public static $module = null;

    // Cuando el login es requerido, por defecto todos los metodos necesitan permisos de lectura y escritura
    // ...excepto los que esten en las listas siguientes: $no_see,$no_edit
    protected $no_see = ['autocomplete']; // No necesitan permisos de lectura
    protected $no_edit = ['index', 'item', 'exportar']; // No necesitan permisos de escritura

    public function __construct()
    {
        self::$module = real_class(static::class);

        if (Auth::init()) {

            if (static::AUTH_REQUIRED && !static::CAN_ALL) {

                $session_limit = STG::num('session_limit');

                if (!Auth::enabled())
                    done('not_enabled');

                if (!in_array(Route::$method, $this->no_see) && !Perms::see($this->modules))
                    done('not_readable');

                if (!in_array(Route::$method, $this->no_edit) && !Perms::can($this->modules))
                    done('not_authorized');

                # validar fecha expiracion
                if ($session_limit && Auth::user()->se_date_expiration < Date::ins()->format())
                    done('session_expired');

                if (static::UPDATE_SESSION_EXPIRATION && $session_limit) {
                    # aqui actualizar la sesion
                    if ($id_session = Auth::$id_session) {
                        QB::table('sessions')
                            ->where('id', $id_session)
                            ->update([
                                'date_expiration' => Raw::dateAdd($session_limit),
                            ]);
                    }
                }

            }

        } else if (static::AUTH_REQUIRED) {
            Util::done_rsp('not_logged');
        }
    }

    public function title()
    {
        return Perms::current() ? Perms::current()->name : stg('brand');
    }

    public function provider()
    {
        return Proveedor::find(Auth::id_proveedor());
    }

}