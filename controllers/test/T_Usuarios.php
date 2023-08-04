<?php namespace Controllers\test;

use Inc\Rsp;
use Libs\Pixie\QB;
use Models\User;

class T_Usuarios extends _controller
{

    public function correoUsuarioCreado(int $id = 1)
    {
        $user = User::find($id);

        if (!$user->exist()) {
            return Rsp::e404();
        } else if ($user->correoUsuarioCreado()) {
            return Rsp::ok()
                ->set('user', $user);
        } else {
            return rsp('Error al enviar');
        }
    }

    public function asignarClavesDemoParaTodos()
    {
        if (!DEBUG) return rsp('only debug mode');

        $password = md5('demo');

        QB::table('users')->where('password', '!=', $password)->update(['password' => $password]);

        return Rsp::ok();
    }

}
