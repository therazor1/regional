<?php namespace Controllers\api;

use Inc\Auth;
use Inc\Push;
use Inc\Rsp;
use Inc\utils\UOrden;
use Libs\Pixie\QB;
use Models\Log;
use Models\Notification;
use Models\User;

class crons extends _controller
{
    const PUSH_CONDUCTOR  = 1;
    const PUSH_SUPERVISOR = 2;

    /**
     * Enviar push diario a los conductores -> a las 10 am
     */
    public function daily()
    {
        $notiConductor = QB::table('predefined_notifications')
            ->select('title, body')
            ->where('state', Notification::_STATE_ENABLED)
            ->where('para', 'c')
            ->first();
        /**
         * Conductores que el día de hoy no han Sync y tienen un token FCM válido
         */
        $qb = QB::query("SELECT 
           us.id,ud.id_dia,di.name dia,di.short_name,sa.hora_inicio,CONCAT(us.name,' ',us.surname) conductor,us.token_fcm
            FROM users us
            LEFT JOIN usuario_dias ud on ud.id_conductor=us.id
            LEFT JOIN dias di on di.id=ud.id_dia
            LEFT JOIN salidas sa on sa.id_conductor=us.id
            WHERE us.id NOT IN (SELECT us.id
            FROM users us
            LEFT JOIN dreams dr ON dr.id_user = us.id
            WHERE date(dr.date_created) = current_date
            AND us.id_role = 5
            AND dr.exists_data = 1)
            AND us.id_role = 5
            AND us.id_type_user = 2
            AND us.token_fcm != ''
            AND di.short_name='" . getDayNow() . "' 
          ");

        /*echo json_encode($qb->get());
        exit;*/

        $conductoresSendPush = $qb->get();
        $bodyPush=$notiConductor->body;
        $titlePush=$notiConductor->title;

        foreach ($conductoresSendPush as $user) {
            $push = new Push();
            $push->body($bodyPush);
            $push->title($titlePush);
            $push->token($user->token_fcm);
            $push->send();

            QB::table('notifications')->insert([
                'id_user' => $user->id,
                'id_type_send' => self::PUSH_CONDUCTOR,
                'title'        => $notiConductor->title,
                'body'         => $titlePush,
            ]);
        }

        Log::add(18, 0, 0, 'cron_dreams_daily_conductores', json_encode([
            'pushNotificationsConductores' => [
                'num_users' => count($conductoresSendPush),
            ],
        ]));

        return Rsp::ok()
            ->set('msg', "Envio de push");
    }


    /**
     *START -> CRON 4AM CONDUCTORES ->HORA SALIDA 5AM
     */
    public function cron_dreams_salida4am()
    {
        $notiConductor = QB::table('predefined_notifications')
            ->select('title, body')
            ->where('state', Notification::_STATE_ENABLED)
            ->where('para', 'c')
            ->first();

        /*CONDUCTORES QUE EL DIA DE HOY(NOW) AÚN NO HAN SINCRONIZADO*/
        $sql = QB::query("SELECT 
           us.id,ud.id_dia,di.name dia,di.short_name,sa.hora_inicio,CONCAT(us.name,' ',us.surname) conductor,us.token_fcm
            FROM users us
            LEFT JOIN usuario_dias ud on ud.id_conductor=us.id
            LEFT JOIN dias di on di.id=ud.id_dia
            LEFT JOIN salidas sa on sa.id_conductor=us.id
            WHERE us.id NOT IN (SELECT us.id
            FROM users us
            LEFT JOIN dreams dr ON dr.id_user = us.id
            WHERE date(dr.date_created) = current_date
            AND us.id_role = 5
            AND dr.exists_data = 1)
            AND us.id_role = 5
            AND us.id_type_user = 2
            AND us.token_fcm != ''
            AND di.short_name='" . getDayNow() . "' 
            AND sa.hora_inicio='05:00'
          ");

        $conductoresSendPush = $sql->get();
        $arr_users           = array_chunk($conductoresSendPush, 950); //partir el array en trozos de 950
        foreach ($arr_users as $_users) {
            $push = new Push();
            //le paso el body al push
            $idConductor='';
            $push->body($notiConductor->body);
            $push->title($notiConductor->title);

            foreach ($_users as $_user) {
                $push->token($_user->token_fcm);
                $idConductor=$_user->id;
            }

            $push->send();

            QB::table('notifications')->insert([
                'id_user' => $idConductor,
                'id_type_send' => self::PUSH_CONDUCTOR,
                'title'        => $notiConductor->title,
                'body'         => $notiConductor->title,
            ]);
        }

        Log::add(18, 0, 0, 'cron_dreams_salida4am_conductores', json_encode([
            'pushNotificationsConductores' => [
                'num_users' => count($conductoresSendPush),
            ],
        ]));

        return Rsp::ok()
            ->set('msg', $push->status_message);
    }

    /**
     *CRON 5AM CONDUCTORES ->HORA SALIDA 6AM
     */
    public function cron_dreams_salida5am()
    {
        $notiConductor = QB::table('predefined_notifications')
            ->select('title, body')
            ->where('state', Notification::_STATE_ENABLED)
            ->where('para', 'c')
            ->first();

        /*CONDUCTORES QUE EL DIA DE HOY(NOW) AÚN NO HAN SINCRONIZADO*/
        $sql = QB::query("SELECT 
           us.id,ud.id_dia,di.name dia,di.short_name,sa.hora_inicio,CONCAT(us.name,' ',us.surname) conductor,us.token_fcm
            FROM users us
            LEFT JOIN usuario_dias ud on ud.id_conductor=us.id
            LEFT JOIN dias di on di.id=ud.id_dia
            LEFT JOIN salidas sa on sa.id_conductor=us.id
            WHERE us.id NOT IN (SELECT us.id
            FROM users us
            LEFT JOIN dreams dr ON dr.id_user = us.id
            WHERE date(dr.date_created) = current_date
            AND us.id_role = 5
            AND dr.exists_data = 1)
            AND us.id_role = 5
            AND us.id_type_user = 2
            AND us.token_fcm != ''
            AND di.short_name='" . getDayNow() . "' 
            AND sa.hora_inicio='06:00'
          ");

        $conductoresSendPush = $sql->get();
        $arr_users           = array_chunk($conductoresSendPush, 950); //partir el array en trozos de 950
        foreach ($arr_users as $_users) {
            $push = new Push();
            //le paso el body al push
            $idConductor='';
            $push->body($notiConductor->body);
            $push->title($notiConductor->title);

            foreach ($_users as $_user) {
                $push->token($_user->token_fcm);
                $idConductor=$_user->id;
            }

            $push->send();

            QB::table('notifications')->insert([
                'id_user' => $idConductor,
                'id_type_send' => self::PUSH_CONDUCTOR,
                'title'        => $notiConductor->title,
                'body'         => $notiConductor->title,
            ]);
        }

        Log::add(18, 0, 0, 'cron_dreams_salida5am_conductores', json_encode([
            'pushNotificationsConductores' => [
                'num_users' => count($conductoresSendPush),
            ],
        ]));

        return Rsp::ok()
            ->set('msg', $push->status_message);
    }

    /**
     *PUSH SUPERVISORES 4:30AM
     */
    public function cron_dreams_salida4_30am_supervisor()
    {
        $notiConductor = QB::table('predefined_notifications')
            ->select('title, body')
            ->where('state', Notification::_STATE_ENABLED)
            ->where('para', 'c')
            ->first();

        /*CONDUCTORES QUE EL DIA DE HOY(NOW) AÚN NO HAN SINCRONIZADO*/
        $sql = QB::query("SELECT cond.*, op.id_supervisor,CONCAT(su.name,' ',su.name) supervisor,su.token_fcm token_fcm_su 
        FROM operaciones op JOIN (SELECT
            us.id,ud.id_dia,di.name dia,di.short_name,sa.hora_inicio,CONCAT(us.name,' ',us.surname) conductor,us.token_fcm
        FROM users us
            LEFT JOIN usuario_dias ud on ud.id_conductor=us.id
            LEFT JOIN dias di on di.id=ud.id_dia
            LEFT JOIN salidas sa on sa.id_conductor=us.id
        WHERE us.id NOT IN (SELECT us.id
            FROM users us
            LEFT JOIN dreams dr ON dr.id_user = us.id
            WHERE date(dr.date_created) = current_date
          AND us.id_role = 5
          AND dr.exists_data = 1)
          AND us.id_role = 5
          AND us.id_type_user = 2
          AND us.token_fcm != ''
          AND di.short_name='" . getDayNow() . "' 
          AND sa.hora_inicio='05:00'
          ) cond
            ON cond.id=op.id_conductor
        JOIN users su ON su.id=op.id_supervisor");

        $conductoresSendPush = $sql->get();

        foreach ($conductoresSendPush as $_users) {
            $push = new Push();
            //PUSH AL SUPERVISOR
            $push->title($notiConductor->title);
            $push->body("El conductor " . $_users->conductor . " no ha sincronizado su registro de sueño");
            $push->token($_users->token_fcm_su);
            $push->send();

            QB::table('notifications')->insert([
                'id_type_send' => self::PUSH_SUPERVISOR,
                'title'        => $notiConductor->title,
                'body'         => "El conductor " . $_users->conductor . " no ha sincronizado su registro de sueño",
            ]);
        }

        Log::add(18, 0, 0, 'salida4_30am_supervisor', json_encode([
            'pushNotificationsSupervidores' => [
                'num_users' => count($conductoresSendPush),
            ],
        ]));

        return Rsp::ok()
            ->set('msg', $push->status_message);
    }

    /**
     *PUSH SUPERVISORES 5:30AM
     */
    public function cron_dreams_salida5_30am_supervisor()
    {
        $notiConductor = QB::table('predefined_notifications')
            ->select('title, body')
            ->where('state', Notification::_STATE_ENABLED)
            ->where('para', 'c')
            ->first();

        /*CONDUCTORES QUE EL DIA DE HOY(NOW) AÚN NO HAN SINCRONIZADO*/
        $sql = QB::query("SELECT cond.*, op.id_supervisor,CONCAT(su.name,' ',su.name) supervisor,su.token_fcm token_fcm_su 
        FROM operaciones op JOIN (SELECT
            us.id,ud.id_dia,di.name dia,di.short_name,sa.hora_inicio,CONCAT(us.name,' ',us.surname) conductor,us.token_fcm
        FROM users us
            LEFT JOIN usuario_dias ud on ud.id_conductor=us.id
            LEFT JOIN dias di on di.id=ud.id_dia
            LEFT JOIN salidas sa on sa.id_conductor=us.id
        WHERE us.id NOT IN (SELECT us.id
            FROM users us
            LEFT JOIN dreams dr ON dr.id_user = us.id
            WHERE date(dr.date_created) = current_date
          AND us.id_role = 5
          AND dr.exists_data = 1)
          AND us.id_role = 5
          AND us.id_type_user = 2
          AND us.token_fcm != ''
          AND di.short_name='" . getDayNow() . "' 
          AND sa.hora_inicio='06:00'
          ) cond
            ON cond.id=op.id_conductor
        JOIN users su ON su.id=op.id_supervisor");


        $conductoresSendPush = $sql->get();

        foreach ($conductoresSendPush as $_users) {
            $push = new Push();
            //PUSH AL SUPERVISOR
            $push->title($notiConductor->title);
            $push->body("El conductor " . $_users->conductor . " no ha sincronizado su registro de sueño");
            $push->token($_users->token_fcm_su);
            $push->send();

            QB::table('notifications')->insert([
                'id_type_send' => self::PUSH_SUPERVISOR,
                'title'        => $notiConductor->title,
                'body'         => "El conductor " . $_users->conductor . " no ha sincronizado su registro de sueño",
            ]);
        }

        Log::add(18, 0, 0, 'salida5_30am_supervisor', json_encode([
            'pushNotificationsSupervidores' => [
                'num_users' => count($conductoresSendPush),
            ],
        ]));

        return Rsp::ok()
            ->set('msg', $push->status_message);
    }

    public function me2(){
        $me=me();
        //dep($me);
        //echo $me;
        dep(Auth::id());
    }

    public function msgTest()
    {
        $sql = QB::query("SELECT 
           ud.id_dia,di.name dia,di.short_name,us.*
            FROM users us
            LEFT JOIN usuario_dias ud on ud.id_conductor=us.id
            LEFT JOIN dias di on di.id=ud.id_dia
            WHERE us.id NOT IN (SELECT us.id
            FROM users us
            LEFT JOIN dreams dr ON dr.id_user = us.id
            WHERE date(dr.date_created) = current_date
            AND us.id_role = 5
            AND dr.exists_data = 1)
            AND us.id_role = 5
            AND us.id_type_user = 2
            AND us.token_fcm != ''
            AND di.short_name='" . getDayNow() . "'
          ")->get();
        dep($sql);
    }

}