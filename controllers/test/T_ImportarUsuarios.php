<?php namespace Controllers\test;

use Inc\Req;
use Inc\Util;
use Models\Obra;
use Models\Role;
use Models\TipoDocumento;
use Models\User;
use Models\UserObra;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class T_ImportarUsuarios extends _controller
{

    public function verificar()
    {

        $file_path = _PATH_ . '/assets/files/importar/usuarios.xlsx';
        $reader = new Xlsx();
        $spreadsheet = $reader->load($file_path);
        $sheet = $spreadsheet->getSheet(0);
        $sheet->removeRow(1); // eliminar la primera fila
        $sheetData = $sheet->toArray();

        $items = [];

        foreach ($sheetData as $a) {
            $rq = new Req($a);

            $item = [
                'ok'                => false,
                'id_obra'           => $rq->num(0),
                'id_role'           => $rq->num(1),
                'id_tipo_documento' => $rq->num(2),
                'document'          => $rq->any(3),
                'name'              => $rq->any(4),
                'surname'           => $rq->any(5),
                'email'             => $rq->any(6),
                'tipo_trabajo'      => $rq->any(7),
                'fecha_ingreso'     => $rq->date(8),
                'fecha_salida'      => $rq->date(9),
            ];

            if ($item['id_obra'] < 0) {
                $item['msg'] = ':id_obra';

            } else if ($item['id_obra'] != 0 && !Obra::find($item['id_obra'])->exist()) {
                $item['msg'] = 'La obra no existe';

            } else if ($item['id_role'] <= 0) {
                $item['msg'] = ':id_role';

            } else if (!Role::find($item['id_role'])->exist()) {
                $item['msg'] = 'El perfil no existe';

            } else if ($item['id_tipo_documento'] <= 0) {
                $item['msg'] = ':id_tipo_documento';

            } else if (!TipoDocumento::find($item['id_tipo_documento'])->exist()) {
                $item['msg'] = 'El tipo de documento no existe';

            } else if ($item['document'] <= 0) {
                $item['msg'] = ':num documento';

            } else if (!$item['name']) {
                $item['msg'] = ':name';

            } else if (!$item['surname']) {
                $item['msg'] = ':surname';

            } else if (!$item['email']) {
                $item['msg'] = ':email';

            } else if (!Util::isEmail($item['email'])) {
                $item['msg'] = 'Formato de correo incorrecto';

            } else {

                $user = User::find([
                    'id_type_user' => User::TYPE_OPERATOR,
                    'email'        => $item['email'],
                    'state'        => User::_STATE_ENABLED,
                ]);

                if ($user->exist()) {
                    $item['msg'] = 'Existe un usuario con el mismo email';

                } else {

                    $user->data('id_type_user', User::TYPE_OPERATOR);
                    $user->data('id_tipo_documento', $item['id_tipo_documento']);
                    $user->data('document', $item['document']);
                    $user->data('name', $item['name']);
                    $user->data('surname', $item['surname']);
                    $user->data('email', $item['email']);
                    $user->data('password', md5('ohl:000'));
                    $user->data('tipo_trabajo', $item['tipo_trabajo']);
                    $user->data('fecha_ingreso', $item['fecha_ingreso']);
                    $user->data('fecha_salida', $item['fecha_salida']);

                    if ($item['id_obra'] == 0) {
                        # ohl central, asignamos el rol
                        $user->data('id_role', $item['id_role']);
                        $user->data('ohl_central', 1);
                    }

                    if ($user->create()) {

                        $item['ok'] = true;

                        if ($item['id_obra'] != 0) {
                            UserObra::insert([
                                'id_user'             => $user->id,
                                'id_obra'             => $item['id_obra'],
                                'id_role'             => $item['id_role'],
                                'id_flujo_aprobacion' => 0,
                            ]);
                        }

                    } else {
                        $item['msg'] = 'Error al crear usuario';
                    }
                }

            }

            $items[] = $item;
        }

        return $items;
    }

    public function cambioEmailUsuarios()
    {

        $file_path = _PATH_ . '/assets/files/importar/cambio_email_usuarios.xlsx';
        $reader = new Xlsx();
        $spreadsheet = $reader->load($file_path);
        $sheet = $spreadsheet->getSheet(0);
        $sheet->removeRow(1); // eliminar la primera fila
        $sheetData = $sheet->toArray();

        $items = [];

        foreach ($sheetData as $a) {
            $rq = new Req($a);

            $item = [
                'ok'          => false,
                'id'          => $rq->num(0),
                'nombre'      => $rq->any(1),
                'apellido'    => $rq->any(2),
                'email'       => $rq->any(3),
                'email_nuevo' => $rq->any(4),
            ];


            if ($item['id'] <= 0) {
                $item['msg'] = ':id';

            } else if (!Util::isEmail($item['email_nuevo'])) {
                $item['msg'] = ':email_nuevo';

            } else {

                $user = User::find($item['id']);

                if (!$user->exist()) {
                    $item['msg'] = 'no existe';

                } else if ($user->email == $item['email_nuevo']) {
                    $item['msg'] = 'mismo email';

                } else {
                    $item['email_actual'] = $user->email;
                    $item['ok'] = true;
                    $user->update(['email' => $item['email_nuevo']]);
                }

                # aca
                $items[] = $item;
            }

        }

        return [
            'items' => $items,
        ];
    }

}