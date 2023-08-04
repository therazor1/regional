<?php namespace Controllers\app;

use Inc\Mailer;
use Inc\Perms;
use Inc\Req;
use Inc\Rsp;
use Inc\STG;
use Inc\Util;
use Libs\Pixie\QB;
use Libs\Pixie\Raw;
use Models\Log;
use Models\Session;
use Models\Token;
use Models\User;

class auth extends _controller
{

    public function __construct()
    {
        parent::__construct(false);
    }

    public function index()
    {
        return view('auth');
    }

    public function login(Req $req)
    {
        $username = $req->any('username');
        $password = $req->any('password');

        if (empty($username)) {
            return rsp('Ingresa un usuario')->set('field', 'username');

        } else if (empty($password)) {
            return rsp('Ingresa una contraseña')->set('field', 'password');

        } else {
            $username = addslashes($username);
            $password = md5(addslashes($password));

            //$user = User::find('email', $username);
            $user = User::find('document', $username);
            //dep($user);
            if (!$user->exist()) {
                return rsp('DNI incorrecto')->set('field', 'username');

            } else if ($user->state == User::_STATE_DELETED) {
                return rsp('Cuenta eliminada');

            } else if ($user->password() != $password) {
                return rsp('Contraseña incorrecta')->set('field', 'password');

            }else if (!in_array($user->id_type_user, [User::TYPE_COLABORADOR, User::TYPE_OPERATOR])) {
                return rsp('Solo pueden acceder los colaboradores'); //aquí esta entrando
            } else {
                $session = $this->_createSession($req, $user->id);

                $salida=QB::table('salidas')
                    ->where('id_conductor', $user->id)
                    ->first();
                $hora_inicio=!empty($salida)?$salida->hora_inicio:'';

                Log::add(Log::LOGIN, $user->id);

                //TODO: Solo al hacer login -> obtenemos el TOKEN "JWT"
                return Rsp::ok()
                    ->set('id', $user->id)
                    ->set('name', $user->name)
                    ->set('surname', $user->surname)
                    ->set('email', $user->email)
                    ->set('phone', $user->phone)
                    ->set('hora_inicio', $hora_inicio)
                    ->set('document', $user->document)
                    ->set('pic', $user->pic)
                    ->set('token', $session->token)
                    ->set('id_fitbit', $user->id_fitbit)
                    ->set('token_fitbit', $user->token_fitbit)
                    ->set('rtoken_fitbit', $user->rtoken_fitbit);

            }

        }
    }

    private function _createSession(Req $req, $id_user)
    {
        $uuid = $req->any('uuid');

        $session = Session::get($uuid, $id_user);
        $session->data('uuid', $uuid);
        $session->data('lat', $req->num('lat'));
        $session->data('lng', $req->num('lng'));
        $session->data('platform', $req->any('platform'));
        $session->data('app_version', $req->any('app_version'));
        $session->data('device_brand', $req->any('device_brand'));
        $session->data('device_model', $req->any('device_model'));
        $session->data('os', $req->any('os'));
        $session->data('os_version', $req->any('os_version'));
        $session->data('language', $req->any('language'));
        $session->data('state', Session::_STATE_ENABLED);

        if (!$session->exist()) {
            $session->data('id_user', $id_user);
            $session->data('token', Util::token($id_user));
        }
        if ($session->save()) {
            return $session;
        } else {
            return null;
        }
    }

    /**
     * Recover password desde APP -> 1ero
     */
    public function recover(Req $req)
    {
        $username = $req->any('username');

        if (empty($username)) {
            return rsp('Correo electrónico incorrecto');
        }

        //$user = User::find('email', $username);
        $user = User::find('document', $username);

        if (!$user->exist()) {
            //return rsp('No hay ninguna cuenta asociada con el correo electrónico.');
            return rsp('No hay ninguna cuenta asociada con el DNI.');

        } else if ($user->state == User::_STATE_DELETED) {
            return rsp('Cuenta eliminada');

        } else {
            $token = new Token();
            $token->data('id_user', $user->id);
            $token->data('type', Token::TYPE_RECOVER);
            $token->data('token', Util::token($username));
            $token->data('date_expiration', Raw::dateAdd(6, 'HOUR'));

            try{
                $user_now = User::find($user->id);
                $user_now->update(['recover_app' => 1]);
            }catch (\Exception $ex){}

            if ($token->save()) {
                /**
                 * URL generado desde el back
                 */
                //$url = $user->url() . '/recover_password/' . $token->token."?app=1";
                $url = $user->url() . '/recover_password/' . $token->token;

                if (Mailer::recoverPassword($user->name, $user->email, $url)) {

                    return Rsp::ok('Recibirás un email con instrucciones para reiniciar tu contraseña en unos minutos.');

                } else {
                    return rsp('Error al enviar correo electrónico');
                }
            } else {
                return rsp('Error interno : DB');
            }
        }
    }

    public function logout()
    {
        if (\Inc\Auth::logged()) {
            Session::where('token', '=', \Inc\Auth::$token)
                ->update([
                    'state' => 0,
                ]);
            Log::add(Log::LOGOUT, \Inc\Auth::id());
        }

        if (isset($_SERVER["HTTP_REFERER"])) {
            header('Location: ' . URL_WEB . '/auth?r=' . base64_encode($_SERVER['HTTP_REFERER']));
        } else {
            header('Location: ' . URL_WEB . '/auth');
        }
    }

}
