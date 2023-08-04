<?php namespace Inc\utils;

use Inc\Req;
use Inc\Util;
use Models\Session;

class USession
{

    /**
     * @param Req $req
     * @param $id_user
     * @return Session|null
     */
    static function create(Req $req, $id_user)
    {
        $uuid = $req->any('uuid');

        $session = Session::get($uuid, $id_user);
        $session->data('uuid', $uuid);
        $session->data('lat', $req->num('lat'));
        $session->data('lng', $req->num('lng'));
        $session->data('platform', $req->any('platform'));
        $session->data('app_version', $req->any('app_version'));
        $session->data('device_brand', $req->any('device_brand'));
        $session->data('device_model', $req->any('device_model'));
        $session->data('os', $req->any('os'));
        $session->data('os_version', $req->any('os_version'));
        $session->data('language', $req->any('language'));
        $session->data('state', Session::_STATE_ENABLED);

        if (!$session->exist()) {
            $session->data('id_user', $id_user);
            $session->data('token', Util::token($id_user));
        }
        if ($session->save()) {
            return $session;
        } else {
            return null;
        }
    }

}