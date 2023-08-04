<?php namespace Controllers\test;

use Inc\Rsp;
use Libs\Pixie\QB;
use Models\User;

class T_General extends _controller
{

    public function pruebaQB()
    {
        $id_orden = '972';
        $id_flujo_aprobacion = '44';
        $id_obra = '10';

        $users = QB::table('user_obras uo')
            ->select([
                'us.id',
                "CONCAT(us.name, ' ', us.surname)" => 'user',
            ])
            ->join('users us', 'us.id', '=', 'uo.id_user')
            ->join('roles ro', 'ro.id', 'IN', '(uo.id_roles)')
            ->where('uo.id_flujo_aprobacion', $id_flujo_aprobacion)
            ->where('uo.id_obra', $id_obra)
            ->where('us.state', User::_STATE_ENABLED)
            ->where('ro.aprobador', 1)
            ->orderBy('uo.orden')
            ->get();

        return $users;
    }

}