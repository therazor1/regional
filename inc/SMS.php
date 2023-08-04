<?php namespace Inc;

use Exception;
use Twilio\Rest\Client;

class SMS
{
    private static $from_number = '+15795890815';

    // Configurar Mailer
    public static function get()
    {
        $account_sid = 'ACd5f375f3c3e7a68836f3f84620a228d8';
        $auth_token = '580128b237e7d8a8b5517034447b56a4';
        try {
            return new Client($account_sid, $auth_token);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function send($to, $body)
    {
        return false; # hasta corregir autenticacion

        if (empty($to)) {
            return false;
        } else {

            if (substr($to, 0, 3) != "+51") {
                $to = '+51' . $to;
            }

            if ($sms = self::get()) {
                $sms->messages->create(
                    $to,
                    array(
                        'from' => self::$from_number,
                        'body' => $body
                    )
                );
                return true;
            } else {
                return false;
            }
        }
    }

    // notificacion
    public static function notification($body)
    {
        $phones = explod(STG::get('notif_recept_phones'));

        if (empty($phones)) return;

        foreach ($phones as $phone) {
            self::send(trim($phone), $body);
        }
    }

}
