<?php namespace Controllers\admin;

use Inc\Auth;
use Inc\Pic;
use Inc\Req;
use Inc\Rsp;
use Libs\Pixie\QB;
use Models\Obra;
use Models\Role;
use Models\User;

class me extends _controller
{
    const CAN_ALL = true;

    public function index(Req $req)
    {
        $me = Auth::user();
        $rsp = Rsp::ok();

        if ($me->tieneClaveGenerica()) {
            $rsp->msg('Debes cambiar tu contraseña para continuar.');
            $rsp->set('cambiar_clave_generica', true);
        }

        return $rsp->merge($me);
    }

    public function update(Req $req)
    {
        $user = $req->get('user');

        QB::table('user_shortcuts')->where('id_user', Auth::id())->delete();

        foreach ($user['data']['shortcuts'] as $id_module) {
            QB::table('user_shortcuts')->insert([
                'id_user'   => Auth::id(),
                'id_module' => $id_module,
            ]);
        }

        return rsp(true);
    }

    public function actualizarPerfil(Req $req)
    {
        $me = Auth::user();

        Pic::img('pic_firma')->db('users', 'pic_firma', $me->id);

        return Rsp::ok();
    }

    public function cambiarClave(Req $req)
    {
        $data = $req->data([
            'current_password' => ['required' => 'Contraseña actual'],
            'new_password_1'   => ['required|min:4' => 'Contraseña nueva'],
            'new_password_2'   => ['required|min:4' => 'Confirma la nueva contraseña'],
        ]);

        $me = Auth::user();

        if ($me->password() != md5($data->current_password)) {
            return rsp('La contraseña actual es incorrecta.');

        } else if ($data->new_password_1 == User::CLAVE_GENERICA) {
            return rsp('No puedes usar esta contraseña.');

        } else if ($data->new_password_1 != $data->new_password_2) {
            return rsp('Las contraseñas no coinciden.');

        } else {

            $me->data('password', md5($data->new_password_1));

            return $me->saveRSP();
        }
    }

}
