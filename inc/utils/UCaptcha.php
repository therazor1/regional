<?php namespace Inc\utils;

use Inc\Rsp;

class UCaptcha
{

    const KEY_FRONT = '6LcUlgYkAAAAAMTnFCbp1y0SlA7jg5hpS1x6M2Zl';
    const KEY_BACK = '6LcUlgYkAAAAAN8KyEH4DPJFJSTdPvso6yFnBQYD';

    static function verify($token)
    {
        # Nota: cuando se consulta con file_get_contents hay errores, en su lugar se usa curl

        $url = 'https://www.google.com/recaptcha/api/siteverify';

        $post_data = http_build_query(
            array(
                'secret'   => self::KEY_BACK,
                'response' => $token,
            )
        );

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        $data = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($data);

        if ($data->success) {
            return Rsp::ok();
        } else {
            return rsp(false, "error")->set('result', $data);
        }
    }

}