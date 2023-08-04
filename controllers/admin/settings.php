<?php namespace Controllers\admin;

use DateTime;
use Inc\Date;
use Inc\installer\Installer;
use Inc\Pic;
use Inc\Req;
use Inc\Rsp;
use Inc\STG;
use Models\Setting;

class settings extends _controller
{

    public function index()
    {
        $datetime = new DateTime('now');

        $installer = Installer::ins();

        return Rsp::item(STG::all())
            ->set('crons', $installer->verificarCrons())
            ->set('codigo_backups_configurado', $installer->codigoBackupsConfigurado())
            ->set('mysql_backups', $installer->leerCopiasDeSeguridad())
            ->set('tiempo', [
                'zona_horaria' => $datetime->getTimezone()->getName(),
                'diferencia'   => $datetime->format('P'),
                'fecha'        => Date::ins($datetime->getTimestamp())->humanDatetime(),
            ]);
    }

    public function save(Req $req)
    {
        $data = [
            'coin'        => $req->any('coin'),
            'interval'    => $req->num('interval'),
            'cms_version' => $req->any('cms_version'),

            'brand'        => $req->any('brand'),
            'ruc'          => $req->any('ruc'),
            'name'         => $req->any('name'),
            'website'      => $req->any('website'),
            'email'        => $req->any('email'),
            'phone'        => $req->any('phone'),
            'country_code' => $req->any('country_code'),
            'lat'          => $req->num('lat'),
            'lng'          => $req->num('lng'),
            'address'      => $req->any('address'),

            'mail_sender'   => $req->any('mail_sender'),
            'mail_bcc'      => $req->any('mail_bcc'),
            'mail_auth'     => $req->bool('mail_auth'),
            'mail_host'     => $req->any('mail_host'),
            'mail_username' => $req->any('mail_username'),
            'mail_password' => $req->any('mail_password'),

            'key_firebase' => $req->any('key_firebase'),
            'key_maps'     => $req->any('key_maps'),
        ];

        if ($pic = Pic::file('pic_logo')->prefix('logo')->go()) {
            $data['pic_logo'] = $pic;
        }

        if ($pic = Pic::file('pic_favicon')->prefix('favicon')->go()) {
            $data['pic_favicon'] = $pic;
        }

        return $this->_saveAndRSP($data);
    }

    private function _saveAndRSP($items)
    {
        foreach ($items as $name => $value) {
            Setting::where('name', $name)->update(['value' => $value]);
        }
        return Rsp::ok();
    }

    public function instalarCron(Req $req)
    {
        $id = $req->num('id');

        $crons = Installer::ins()->crons();

        if (!isset($crons[$id])) {
            return rsp('No existe el cron.');

        } else {
            $cron = $crons[$id];
            Installer::ins()->instalarCron($cron);
            return Rsp::ok('Instalado correctamente.');
        }
    }

    public function eliminarCron(Req $req)
    {
        $id = $req->id();

        $crons = Installer::ins()->crons();

        if (!isset($crons[$id])) {
            return rsp('No existe el cron.');

        } else {
            $cron = $crons[$id];
            Installer::ins()->eliminarCron($cron);
            return Rsp::ok('Eliminado correctamente.');
        }
    }

    public function guardarConfig(Req $req)
    {
        $tipo = $req->any('tipo');

        switch ($tipo) {
            case 'crear_codigo_backup':
                Installer::ins()->crearCopiasDeSeguridad();
                return Rsp::ok('Código de copia de seguridad creado.');
            case 'eliminar_codigo_backup':
                $path = Installer::ins()->pathCodeBackupsMysql();
                if (unlink($path)) {
                    return Rsp::ok('Código eliminado.');
                } else {
                    return rsp('Error al eliminar.');
                }
            case 'instalar_cron':
                $id = $req->id();
                $crons = Installer::ins()->crons();

                if (!isset($crons[$id])) {
                    return rsp('No existe el cron.');

                } else {
                    $cron = $crons[$id];
                    Installer::ins()->instalarCron($cron);
                    return Rsp::ok('Cron instalado.');
                }
            case 'eliminar_cron':
                $id = $req->id();
                $crons = Installer::ins()->crons();

                if (!isset($crons[$id])) {
                    return rsp('No existe el cron.');

                } else {
                    $cron = $crons[$id];
                    Installer::ins()->eliminarCron($cron);
                    return Rsp::ok('Cron eliminado.');
                }
            default:
                return rsp('not_defined');
        }
    }

}