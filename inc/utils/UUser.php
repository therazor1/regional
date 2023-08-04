<?php namespace Inc\utils;

use Inc\Mailer;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\Raw;
use Models\Token;
use Models\User;

class UUser
{
    static function recoverRSP($email, $type)
    {
        $user = User::where('email', $email)
            #->where('type', $type) # el email debe ser unico
            ->first();

        if ($user) {
            $token = new Token();
            $token->data('id_user', $user->id);
            $token->data('type', Token::TYPE_RECOVER);
            $token->data('token', Util::token($user->email));
            $token->data('date_expiration', Raw::dateAdd(6, 'HOUR'));

            if ($token->save()) {

                $url = URL_WEB . '/password/change/' . $token->token;

                if (Mailer::recoverPassword($user->name, $user->email, $url)) {
                    return Rsp::ok('Recibirás un email con instrucciones para reiniciar tu contraseña en unos minutos.');
                } else {
                    return rsp('Error al enviar correo electrónico');
                }
            } else {
                return rsp('Error interno : DB');
            }
        } else {
            return rsp('No hay ninguna cuenta asociada con el correo electrónico.');
        }
    }

}