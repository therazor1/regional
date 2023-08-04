<?php namespace Models;

use Inc\Bases\BaseModel;
use Inc\Mailer;
use Inc\SMS;

class Notification extends BaseModel
{
    protected $table = 'notifications';

    const STATE = null;
    /*const ESTADO_ELIMINADO = '0';
    const ESTADO_PENDIENTE = '1';
    const ESTADO_ENTREGADO = '2';
    const ESTADO_LEIDO = '3';*/

    const TYPE_OT_CREATED = 1;
    const TYPE_OT_CREATED_PENDING = 4;
    const TYPE_VEHICLE_SOAT_EXPIRATION = 2;
    const TYPE_VEHICLE_RESERVED = 3;
    const TYPE_KIT_NEXT_MAINTENANCE = 4;
    const TYPE_KIT_NEXT_REVIEWED = 5;
    const TYPE_OT_FINALIZED_NOT_INVOICES = 6;


    public $id;
    public $id_user;
    public $id_operator;

    public $id_reference;
    public $id_type_send;
    public $title;
    public $body;
    public $state;
    //public $fecha_creado;


    // CUSTOM
    public static function addOtCreated($id)
    {
        $nt = new Notification();
        $nt->data('type', Notification::TYPE_OT_CREATED);
        $nt->data('id_ref', $id);
        $nt->data('body', 'Se ha creato la OT <b>#' . sprintf('%07d', $id) . '</b>');
        $nt->data('url', '/ots/form/' . $id);
        $nt->create();

        //Mailer::notification($nt->body, $nt->url);
        Ot::sendEmailCreated($id);
        SMS::notification("OT Creada #$id\n" . URL_WEB . '#' . $nt->url);
    }

    public static function addOtCreatedPending($id)
    {
        $nt = new Notification();
        $nt->data('type', Notification::TYPE_OT_CREATED_PENDING);
        $nt->data('id_ref', $id);
        $nt->data('body', 'La OT <b>#' . sprintf('%07d', $id) . '</b> está pendiente de aprobación');
        $nt->data('url', '/ots/approve/' . $id);
        $nt->create();

        Mailer::notification($nt->body, $nt->url);
    }

    public static function addVehicleReservedOT($id_ot, $id_vehicle)
    {
        $nt = new Notification();
        $nt->data('type', Notification::TYPE_VEHICLE_RESERVED);
        $nt->data('id_ref', $id_vehicle);
        $nt->data('body', 'Se ha reservado el vehículo <b>#' . $id_vehicle . '</b> para la OT <b>#' . sprintf('%07d', $id_ot) . '</b>');
        $nt->data('url', '/ots/form/' . $id_ot);
        $nt->create();

        Mailer::notification($nt->body, $nt->url);
    }

    public static function addVehicleSoatExpiration($id, $brand, $model, $plate, $soat_date)
    {
        $nt = new Notification();
        $nt->data('type', Notification::TYPE_VEHICLE_SOAT_EXPIRATION);
        $nt->data('id_ref', $id);
        $nt->data('body', 'El SOAT del vehículo <b>' . $brand . ' ' . $model . '</b> con matrícula <b>' . $plate . '</b> está por caducar');
        $nt->data('url', '/vehicles/' . $id);
        $nt->create();

        Mailer::notification($nt->body, $nt->url);
    }

    public static function addKitNextMaintenance($id, $name)
    {
        $nt = new Notification();
        $nt->data('type', Notification::TYPE_KIT_NEXT_MAINTENANCE);
        $nt->data('id_ref', $id);
        $nt->data('body', 'El próximo mantenimiento para el equipo <b>' . $name . '</b> se acerca');
        $nt->data('url', '/kits/' . $id);
        $nt->create();

        Mailer::notification($nt->body, $nt->url);
    }

    public static function addKitNextReviewed($id, $name)
    {
        $nt = new Notification();
        $nt->data('type', Notification::TYPE_KIT_NEXT_REVIEWED);
        $nt->data('id_ref', $id);
        $nt->data('body', 'La próxima calibración para el equipo <b>' . $name . '</b> se acerca');
        $nt->data('url', '/kits/' . $id);
        $nt->create();

        Mailer::notification($nt->body, $nt->url);
    }

    public static function addOtFinalizedNotInvoices($id, $user)
    {
        $nt = new Notification();
        $nt->data('type', Notification::TYPE_OT_FINALIZED_NOT_INVOICES);
        $nt->data('id_ref', $id);
        $nt->data('body', $user . ' ha finalizado la OT <b>#' . sprintf('%07d', $id) . '</b>, pero no se han agregado facturas.');
        $nt->data('url', '/ots/form/' . $id . '/invoices');
        $nt->create();

        Mailer::notification($nt->body, $nt->url);
    }

}
