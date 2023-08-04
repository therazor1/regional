<?php namespace Inc;

use Inc\Bases\BaseModel;
use Models\Session;
use Models\User;

class Auth
{

    /* @var User */
    public static $user = null;
    public static $id_session = 0;

    public static $token = null;

    /**
     * @param null $closure
     * @param string $token
     * @return bool
     */
    public static function init($closure = null, $token = '')
    {
        # El metodo init solo se puede llamar una vez
        if (self::logged()) exit('Ya fue logeado antes: ' . self::id());

        # Buscar en Parametros
        if (empty($token)) {
            $token = _REQ('token');
        }

        # Buscar en Headers
        if (empty($token)) {
            $token = self::getBearerToken();
        }

        if (!empty($token)) {

            if (is_callable($closure)) {
                $user = $closure($token);
            } else {
                $user = User::queryFirst("
                    SELECT us.*,
                           se.id se_id,
                           se.date_expiration se_date_expiration,
                           ro.id ro_id,
                           ro.id_module ro_id_module,
                           ro.name ro_name,
                           ro.menu_collapsed ro_menu_collapsed
                    FROM sessions se
                        JOIN users us ON us.id = se.id_user
                            JOIN roles ro ON ro.id = us.id_role
                    WHERE se.token = '$token' AND se.state = " . Session::_STATE_ENABLED . "
                    GROUP BY us.id
                ");
            }

            if ($user && $user->exist()) {
                self::$user = $user;
                self::$token = $token;
                self::$id_session = $user->se_id;
                return true;
            }
        }

        return false;
    }

    /**
     * Get hearder Authorization
     * */
    static function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * get access token from header
     * */
    static function getBearerToken()
    {
        $headers = self::getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return '';
    }

    public static function logged()
    {
        return self::user() && self::id() > 0;
    }

    public static function id()
    {
        return self::$user->id;
    }

    public static function id_obra()
    {
        return self::$user->id_obra;
    }

    public static function id_area()
    {
        return self::$user->id_area;
    }

    public static function id_proveedor()
    {
        return self::$user->id_proveedor;
    }

    public static function id_management()
    {
        return self::$user->id_management;
    }

    public static function state()
    {
        return self::$user->state;
    }

    public static function enabled()
    {
        return self::state() == BaseModel::_STATE_ENABLED;
    }

    public static function root()
    {
        return self::logged() && self::user()->root();
    }

    public static function user()
    {
        return self::$user;
    }

}
