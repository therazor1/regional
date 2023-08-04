<?php namespace Controllers\api;

use Inc\Req;
use Models\Token;
use Models\User;

class recover_password extends _controller
{

    public function index(Req $req)
    {
        $token = $req->any('token');
        $rsp = $this->_validateToken($token);

        $view = view('general/recover_password');

        if ($rsp->ok) {
            $view->set('token', $token);
        } else {
            $view->set('msg', $rsp->msg);
        }

        return $view;
    }

    public function save(Req $req)
    {
        $token = $req->any('token');
        $password = $req->any('password');
        $password2 = $req->any('password2');

        $rsp = $this->_validateToken($token);

        if (!$rsp->ok) {
            return $rsp;

        } else if (empty($password)) {
            return rsp('Ingrese su nueva contraseña');

        } else if (empty($password2)) {
            return rsp('Repita su nueva contraseña');

        } else if ($password != $password2) {
            return rsp('Las contraseñas no son iguales');

        } else {
            $item = Token::find('token', $token);
            if (User::where('id', $item->id_user)->update([
                'password' => md5($password),
            ])) {
                $item->delete();
                return rsp(true)
                    ->set('msg', 'Su contraseña fue cambiada correctamente');
            } else {
                return rsp('Error al cambiar la contraseña');
            }
        }
    }

    private function _validateToken($token)
    {

        if (empty($token)) {
            return rsp('Acceso denegado');
        } else {
            $item = Token::find('token', $token);
            if ($item->exist()) {
                if ($item->type == Token::TYPE_RECOVER) {
                    if (strtotime($item->date_expiration) > time()) {
                        if ($item->state == Token::_STATE_ENABLED) {
                            return rsp(true);
                        } else {
                            return rsp('Este enlace ya se usó una vez');
                        }
                    } else {
                        return rsp('El enlace ha expirado');
                    }
                } else {
                    return rsp('El enlace está corrupto');
                }
            } else {
                return rsp('Este enlace no es reconocido');
            }
        }
    }

}