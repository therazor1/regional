<?php namespace Inc\utils;

use Inc\Mailer;
use Inc\MpdfCustom;
use Libs\Pixie\QB;
use Models\Empresa;
use Models\Obra;
use Models\Orden;
use Models\OrdenAprobacion;
use Models\PlazoPago;
use Models\Proveedor;
use Models\TipoImpuesto;
use Models\TipoMoneda;
use Models\User;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

class UOrden
{
    private static function _dataOrden(Orden $orden)
    {
        $obra = Obra::find($orden->id_obra);
        $proveedor = Proveedor::find($orden->id_proveedor);
        $tipo_moneda = TipoMoneda::find($orden->id_tipo_moneda);
        $tipo_impuesto = TipoImpuesto::find($orden->id_tipo_impuesto);
        $user = User::find($orden->id_user);
        $tipo_moneda_principal = TipoMoneda::principal();
        $plazo_pago = PlazoPago::find($orden->id_plazo_pago);

        $empresa = Empresa::find($obra->id_empresa);

        $orden->forma_pago_txt = Orden::formaPagoTxt($orden->forma_pago);

        # obtener los representantes de la obra
        $obra_usuarios = QB::table('users us')
            ->select('us.*')
            ->where('us.id_proveedor', $proveedor->id)
            ->where('us.state', User::_STATE_ENABLED)
            ->get();

        $contacto_proveedor = User::find([
            'id_proveedor'       => $proveedor->id,
            'id_type_user'       => User::TYPE_PROVIDER,
            'state'              => User::_STATE_ENABLED,
            'contacto_principal' => 1,
        ]);

        # por ahora si no hay contacto principal buscamos lo normal
        if (!$contacto_proveedor->exist()) {
            $contacto_proveedor = User::find([
                'id_proveedor' => $proveedor->id,
                'id_type_user' => User::TYPE_PROVIDER,
                'state'        => User::_STATE_ENABLED,
            ]);
        }

        $orden_materiales = QB::table('orden_materiales om')
            ->select([
                'om.*',
                'ma.nombre' => 'ma_nombre',
                'ma.codigo' => 'ma_codigo',
                'un.nombre' => 'un_nombre',

                'eq.codigo' => 'eq_codigo',
                'eq.nombre' => 'eq_nombre',
            ])
            ->join('materiales ma', 'ma.id', '=', 'om.id_material')
            ->join('unidades un', 'un.id', '=', 'ma.id_unidad')
            ->leftJoin('equipos eq', 'eq.id', '=', 'om.id_equipo')
            ->where('om.id_orden', $orden->id)
            ->get();

        # obtener las firmas (aprobaciones) de la orden, no importa si no fue aprobada aun, igual será mostrado
        # solo mostrar la firma si la orden fue firmada
        if ($orden->estado == Orden::ESTADO_APROBADA) {
            $orden_aprobacion = QB::table('orden_aprobaciones oa')
                ->select([
                    'oa.*',
                    'us.name'      => 'us_name',
                    'us.surname'   => 'us_surname',
                    'us.pic_firma' => 'us_pic_firma'
                ])
                ->join('users us', 'us.id', '=', 'oa.id_user')
                ->where('oa.id_orden', $orden->id)
                ->where('oa.estado', OrdenAprobacion::ESTADO_APROBADO)
                ->orderBy('oa.id', 'DESC')
                ->first();

            if ($orden_aprobacion) {
                $orden_aprobacion->aprobado = ($orden_aprobacion->estado == OrdenAprobacion::ESTADO_APROBADO);
            }
        } else {
            $orden_aprobacion = null;
        }

        $monto_base_no_afecto = 0;
        $monto_base_afecto = 0;

        if ($tipo_impuesto->porcentaje == 0) {
            $monto_base_no_afecto = $orden->monto_base;
        } else {
            $monto_base_afecto = $orden->monto_base;
        }

        return [
            'orden'                 => $orden,
            'empresa'               => $empresa,
            'obra'                  => $obra,
            'obra_usuarios'         => $obra_usuarios,
            'proveedor'             => $proveedor,
            'orden_materiales'      => $orden_materiales,
            'contacto_proveedor'    => $contacto_proveedor,
            'orden_aprobacion'      => $orden_aprobacion,
            'tipo_moneda'           => $tipo_moneda,
            'tipo_moneda_principal' => $tipo_moneda_principal,
            'tipo_impuesto'         => $tipo_impuesto,
            'user'                  => $user,
            'plazo_pago'            => $plazo_pago,
            'tipo_cambio'           => $orden->tipo_cambio,
            'monto_base_no_afecto'  => $monto_base_no_afecto,
            'monto_base_afecto'     => $monto_base_afecto,
        ];
    }

    static function exportarOrdenCompra(Orden $orden, $path = null)
    {
        $data_orden = self::_dataOrden($orden);

        $cont_css = file_get_contents(_PATH_ . '/views/general/orden/orden.css')
            . file_get_contents(_PATH_ . '/views/general/orden/compra/orden_compra.css');

        $cont_html = view('general/orden/compra/orden_compra', $data_orden);
        $cont_html_footer = view('general/orden/compra/orden_compra_footer', $data_orden);

        $view = _GET_INT('view');

        if ($view == 1) {
            exit('
                <!doctype html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Document</title>
                    <style type="text/css">
                        ' . $cont_css . '
                    </style>
                </head>
                <body style="padding:20px;margin:0">
                    <div style="max-width:780px">
                        ' . $cont_html . '
                        ' . $cont_html_footer . '
                    </div>
                </body>
                </html>
            ');
        }

        $mpdf = new MpdfCustom([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 6,
            'margin_right'  => 6,
            'margin_top'    => 10,
            'margin_bottom' => 6,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        if ($orden->estado == Orden::ESTADO_ANULADA) {
            $mpdf->showWatermarkText = true;
            $mpdf->SetWatermarkText('NO VÁLIDA');
        } else if ($orden->estado != Orden::ESTADO_APROBADA) {
            $mpdf->showWatermarkText = true;
            $mpdf->SetWatermarkText('BORRADOR');
        }


        $mpdf->SetHeader('Página {PAGENO} de {nb}');

        $mpdf->WriteHTML($cont_css, HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($cont_html, HTMLParserMode::HTML_BODY);
        $mpdf->SetHTMLFooter($cont_html_footer);
        /*$mpdf->AddPage();
        $mpdf->WriteHTML(view('general/orden/compra/anexo-1', $data_orden), HTMLParserMode::HTML_BODY);
        $mpdf->AddPage();
        $mpdf->WriteHTML(view('general/orden/anexo_instrucciones', $data_orden), HTMLParserMode::HTML_BODY);*/

        if ($orden->estado != Orden::ESTADO_APROBADA) {
            /*$x = 13;
            $y = 30;
            $mpdf->SetFont('Arial', size: 154, style: 'B');
            $mpdf->SetTextColor('#000000');
            $mpdf->SetAlpha(0.2);
            $mpdf->Rotate(-58, $x, $y);
            $mpdf->Text($x, $y, 'BORRADOR');
            $mpdf->Rotate(0);*/
        }

        if (is_null($path)) {
            $mpdf->Output('orden.pdf', 'I');
        } else {
            $mpdf->Output($path);
        }
    }

    static function mailOrdenesAprobacionPendiente($id_user = 0)
    {
        # correo para aprobaciones pendientes
        $qb = QB::table('orden_aprobaciones oa');
        $qb->select([
            'us.id'      => 'us_id',
            'us.name'    => 'us_name',
            'us.surname' => 'us_surname',
            'us.email'   => 'us_email',

            'od.id'          => 'od_id',
            'od.correlativo' => 'od_correlativo',

            'ob.id'     => 'ob_id',
            'ob.nombre' => 'ob_nombre',

            'em.id'     => 'em_id',
            'em.nombre' => 'em_nombre',
        ]);
        $qb->join('ordenes od', 'od.id', '=', 'oa.id_orden');
        $qb->join('obras ob', 'ob.id', '=', 'od.id_obra');
        $qb->join('empresas em', 'em.id', '=', 'ob.id_empresa');
        $qb->join('users us', 'us.id', '=', 'oa.id_user');
        $qb->where('oa.estado', OrdenAprobacion::ESTADO_PENDIENTE);

        if ($id_user > 0)
            $qb->where('oa.id_user', $id_user);

        $users = [];

        foreach ($qb->get() as $o) {

            if (!isset($users['user_' . $o->us_id])) {

                $user = new User();
                $user->id = $o->us_id;
                $user->name = $o->us_name;
                $user->surname = $o->us_surname;
                $user->email = $o->us_email;

                $users['user_' . $o->us_id] = [
                    'user'    => $user,
                    'ordenes' => [],
                ];
            }

            $users['user_' . $o->us_id]['ordenes'][] = $o;
        }

        foreach ($users as $item) {

            /** @var User $user */
            $user = $item['user'];
            /** @var Orden[] $ordenes */
            $ordenes = $item['ordenes'];

            Mailer::ordenesAprobacionPendiente($user, $ordenes);

        }

        return array_values($users);
    }
}