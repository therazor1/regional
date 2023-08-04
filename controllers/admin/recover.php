<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Models\Token;
use Models\User;

class recover extends _controller
{
    const AUTH_REQUIRED = false;

    public function index(Req $req)
    {
        return $this->_dalidateToken($req);
    }

    private function _dalidateToken(Req $req)
    {
        $token_recover = $req->any('token_recover');

        if (empty($token_recover)) {
            return rsp('Solicitud incorrecta');
        } else {
            $token = Token::get($token_recover);
            if ($token->exist()) {
                if ($token->state != Token::_STATE_ENABLED) {
                    return rsp('Este enlace ya ha sido utilizado.');

                } else if ($token->type != Token::TYPE_RECOVER) {
                    return rsp('Token incorrecto');

                } else if ($token->hasExpired()) {
                    return rsp('Este enlace ya ha caducado.');

                } else {
                    return rsp(true)->set('token', $token);
                }
            } else {
                return rsp('Este enlace ya ha sido utilizado o ha caducado.');
            }
        }
    }

    /**
     * En el form password1 | password2 -> click btn cambiar pass  | Ultima ACCION USER
     */
    public function save(Req $req)
    {
        $rsp = $this->_dalidateToken($req);

        $password1 = $req->any('password1');
        $password2 = $req->any('password2');

        if (!$rsp->ok) {
            return $rsp;

        } else if (empty($password1)) {
            return rsp('Ingrese su nueva contraseña');

        } else if (empty($password2)) {
            return rsp('Repita su nueva contraseña');

        } else if ($password1 != $password2) {
            return rsp('Las contraseñas no coinciden');

        } else {
            $user = User::find($rsp->token->id_user);
            if ($user->exist()) {

                if ($user->update(['password' => md5($password1)])) {
                    if ($rsp->token->update(['state' => Token::_STATE_DISABLED])) {
                        /**
                         * Rta success cambio de password -> redirect 
                         */
                        $is_app=false;
                        $msgRecover='Tu contraseña ha sido cambiada exitosamente';
                        try{
                            $user_recover_app=$user->recover_app;
                            if(!empty($user_recover_app)){
                                if($user_recover_app){
                                    $is_app=true;
                                    $msgRecover='Tu contraseña ha sido cambiada exitosamente. Vuelve al app para poder acceder';
                                }
                            }
                        }catch (\Exception $ex){}
                        /*return Rsp::ok('Tu contraseña ha sido cambiada exitosamente.')
                            ->set('base_dir', $user->baseDir());*/
                        $user->update(['recover_app' => 0]);

                        return Rsp::ok($msgRecover)
                            ->set('base_dir', $user->baseDir())
                            ->set('is_app', $is_app);
                    } else {
                        return rsp('Error interno : DB');
                    }
                } else {
                    return rsp('Error interno : DB');
                }
            } else {
                return rsp('Error al vincular usuario.');
            }
        }
    }

}