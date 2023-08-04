<?php namespace Controllers\test;

use Inc\Mailer;
use Inc\utils\UOrden;
use Models\OtGasto;
use Models\User;

class T_Mailer extends _controller
{
    public function __construct()
    {
        parent::__construct();
        Mailer::$show = true;
    }

    public function recoverPassword($name = 'Alvaro', $email = 'alvaro@demo.com')
    {
        return Mailer::recoverPassword($name, $email, URL_API . '/confirm');
    }

    public function providerInvitation($name = 'Alvaro', $email = 'alvaro@demo.com')
    {
        return Mailer::providerInvitation($name, $email, 'test001');
    }

    public function ordenesAprobacionPendiente($id_user = '99')
    {
        return UOrden::mailOrdenesAprobacionPendiente($id_user);
    }

    public function otGastoRechazado($id_ot_gasto = '12')
    {
        $ot_gasto = OtGasto::find($id_ot_gasto);
        $user = User::find($ot_gasto->id_user);

        return Mailer::otGastoRechazado($user, $ot_gasto);
    }

}
