<?php namespace Inc;

use Inc\Bases\BaseMailer;
use Models\Material;
use Models\Orden;
use Models\OtGasto;
use Models\User;

class Mailer extends BaseMailer
{
    public static $show = false;

    public static function recoverPassword($name, $email, $url)
    {
        return self::ins($email, $name)
            ->subject('Recuperar contraseña en ' . stg('brand'))
            ->view('recover_password', [
                'name'  => $name,
                'email' => $email,
                'url'   => $url,
            ])->send();
    }

    public static function usuarioCreado($name, $email, $url)
    {
        return self::ins($email, $name)
            ->subject('Cuenta creada en ' . stg('brand'))
            ->bcc('compraschile@ohla-chile.cl')
            ->view('usuario_creado', [
                'name'  => $name,
                'email' => $email,
                'url'   => $url,
            ])->send();
    }

    public static function usuarioCambioPerfil($name, $email)
    {
        return self::ins($email, $name)
            ->subject('Cambio de Perfil en ' . stg('brand'))
            ->bcc('compraschile@ohla-chile.cl')
            ->view('usuario_cambio_perfil', [
                'name'  => $name,
                'email' => $email,
            ])->send();
    }

    public static function providerInvitation($name, $email, $password)
    {
        return self::ins($email, $name)
            ->subject('Invitación ' . stg('brand'))
            ->view('provider_invitation', [
                'name'     => $name,
                'email'    => $email,
                'password' => $password,
                'url'      => STG::ins()->url_proveedores . '/login',
            ])->send();
    }

    public static function materialAprobado($id_material)
    {
        $material = Material::find($id_material);
        $user = User::find($material->id_user);
        return self::ins($user->email, $user->name)
            ->subject('Aprobación de creación de código')
            ->view('material_aprobado', [
                'name'        => $user->name,
                'surname'     => $user->surname,
                'descripcion' => $material->descripcion,
            ])->send();
    }

    public static function materialRechazado($id_material)
    {
        $material = Material::find($id_material);
        $user = User::find($material->id_user);
        return self::ins($user->email, $user->name)
            ->subject('Rechazo creación de código')
            ->view('material_rechazado', [
                'name'             => $user->name,
                'surname'          => $user->surname,
                'motivo_rechazado' => $material->motivo_rechazado,
            ])->send();
    }

    public static function convocatoriaProveedor($nombre, $emails)
    {
        return self::ins()
            ->tos($emails)
            ->subject('Invitación Participar en licitación')
            ->view('convocatoria_proveedor', [
                'nombre' => $nombre,
            ])->send();
    }

    public static function proveedorAceptadoConvocatoria($nombre, $emails)
    {
        return self::ins()
            ->tos($emails)
            ->subject('Orden adjudicada')
            ->view('proveedor_aceptado_convocatoria', [
                'nombre' => $nombre,
            ])->send();
    }

    public static function ordenAprobacionPendiente(Orden $orden, User $user)
    {
        return self::ins($user->email, $user->name)
            ->subject('Orden ' . $orden->correlativo . ' pendiente de aprobación')
            ->view('orden_aprobacion_pendiente', [
                'nombre' => $user->name,
            ])->send();
    }

    public static function ordenAprobacionRechazada(Orden $orden, User $user, $motivo)
    {
        return self::ins($user->email, $user->name)
            ->subject('Orden ' . $orden->correlativo . ' rechazada')
            ->view('orden_aprobacion_rechazada', [
                'nombre' => $user->name,
                'orden'  => $orden,
                'motivo' => $motivo,
            ])->send();
    }

    public static function ordenesAprobacionPendiente(User $user, $ordenes = [])
    {
        $num_ordenes = count($ordenes);
        return self::ins($user->email, $user->name)
            ->subject('Órdenes pendientes de aprobación: ' . $num_ordenes)
            ->view('ordenes_aprobacion_pendiente', [
                'nombre'      => $user->name,
                'num_ordenes' => $num_ordenes,
                'ordenes'     => $ordenes,
            ])->send();
    }

    public static function otGastoRechazado(User $user, OtGasto $otGasto)
    {
        return self::ins($user->email, $user->name)
            ->subject('Gasto rechazado')
            ->view('ot_gasto_rechazado', [
                'user'     => $user,
                'ot_gasto' => $otGasto,
            ])->send();
    }


    // Correo de notificacion
    public static function notification($body, $url, $button = '')
    {
        $emails = explod(STG::get('notif_recept_emails'));

        if (empty($emails)) return false;

        $mailer = Mailer::get();
        foreach ($emails as $email) {
            if (!empty($email)) {
                $mailer->addCC(trim($email));
            }
        }
        $mailer->Subject = STG::get('brand');
        $mailer->msgHTML(view('emails/notification', [
            'body'   => $body,
            'url'    => URL_WEB . '#' . $url,
            'button' => $button
        ]));
        return ($mailer->send());
    }

}
