<?php namespace Controllers\admin;

use Inc\Mailer;
use Inc\Perms;
use Inc\Req;
use Inc\Rsp;
use Inc\STG;
use Inc\Util;
use Inc\utils\UCaptcha;
use Libs\Pixie\Raw;
use Models\Log;
use Models\Session;
use Models\Token;
use Models\User;

class auth extends _controller
{
    const AUTH_REQUIRED = false;

    public function index()
    {
        return view('auth');
    }

    public function login(Req $req)
    {
        $username = $req->any('username');
        $password = $req->any('password');
        $token_captcha = $req->any('token_captcha');

        if (empty($username)) {
            return rsp('Ingresa un usuario')->set('field', 'username');

        } else if (empty($password)) {
            return rsp('Ingresa una contraseña')->set('field', 'password');

        } else if (!$token_captcha) {
            return rsp('Validación incorrecta');

        } else {
            $username = addslashes($username);
            $password = md5(addslashes($password));

            $rc = UCaptcha::verify($token_captcha);
            if (!$rc->ok) return $rc;

            $user = User::find('email', $username);

            if (!$user->exist()) {
                return rsp('Correo electrónico incorrecto')->set('field', 'username');

            }else if ($user->password() != $password) {
                return rsp('Contraseña incorrecta')->set('field', 'password');
            }else if ($user->state == User::_STATE_DELETED) {
                return rsp('Cuenta eliminada');
            } else {
                $uuid = DEBUG ? Session::debugUUID() : $req->any('uuid');
                /*Se crea la sesion*/
                $session = Session::get($uuid, $user->id);
                $session->data('platform', 'web');
                $session->data('uuid', $uuid);
                $session->data('os', $req->any('os'));
                $session->data('language', $req->any('language'));
                $session->data('os_version', $req->any('os_version'));
                $session->data('user_agent', $req->any('user_agent'));
                $session->data('app_version', stg('cms_version'));
                $session->data('state', Session::_STATE_ENABLED);

                if ($session_limit = STG::num('session_limit'))
                    $session->data('date_expiration', Raw::dateAdd($session_limit));

                if (!$session->exist()) {
                    $session->data('id_user', $user->id);
                    $session->data('token', Util::token($user->id));
                }

                if ($session->save()) {
                    if (\Inc\Auth::init(null, $session->token)) {
                        Perms::newIns(); # recargar los permisos

                        Log::add(Log::LOGIN, $user->id);

                        return Rsp::ok()
                            ->set('id', $user->id)
                            ->set('name', $user->name)
                            ->set('surname', $user->surname)
                            ->set('email', $user->email)
                            ->set('phone', $user->phone)
                            ->set('document', $user->document)
                            ->set('pic', $user->pic)
                            ->set('token', $session->token)
                            ->set('ro_name', \Inc\Auth::user()->ro_name);
                    } else {
                        return rsp('Error al autenticar');
                    }
                } else {
                    return Rsp::e500();
                }
            }
        }
    }

    public function recover(Req $req)
    {
        $username = $req->any('username');

        if (empty($username)) {
            return rsp('Correo electrónico incorrecto');
        }

        $user = User::find('email', $username);

        if (!$user->exist()) {
            return rsp('No hay ninguna cuenta asociada con el correo electrónico.');

        } else if ($user->state == User::_STATE_DELETED) {
            return rsp('Cuenta eliminada');

        } else {
            $token = new Token();
            $token->data('id_user', $user->id);
            $token->data('type', Token::TYPE_RECOVER);
            $token->data('token', Util::token($username));
            $token->data('date_expiration', Raw::dateAdd(6, 'HOUR'));

            if ($token->save()) {

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
