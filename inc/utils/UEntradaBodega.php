<?php namespace Inc\utils;

use Inc\Util;
use Models\Material;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class UEntradaBodega
{

    public static function verificar($file_path)
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($file_path);
        $sheet = $spreadsheet->getSheet(0);
        $datas = $sheet->toArray();
        array_shift($datas); # eliminamos el header

        $items = [];

        foreach ($datas as $i_row => $rows) {
            # el primero es DNI del condutor
            # indice 0: nombre
            # indice 1: dni
            $item = (object)[
                'id_material'       => '',
                'ma_codigo'         => @$rows[0] ?: '',
                'ma_nombre'         => '',
                'guia_remision'     => @$rows[1] ?: '',
                'cantidad_ingresar' => @$rows[2] ?: '',
                'precio_unidad'     => @$rows[3] ?: '',
                'fecha_ingreso'     => @$rows[4] ?: '',
                'observaciones'     => @$rows[5] ?: '',
                'zona'              => @$rows[6] ?: '',
                'modulo'            => @$rows[7] ?: '',
                'estante'           => @$rows[8] ?: '',
                'fecha_caducidad'   => @$rows[9] ?: '',
                'lote'              => @$rows[10] ?: '',
                'errores'           => [],
            ];

            if ($item->ma_codigo || $item->guia_remision || $item->cantidad_ingresar || $item->precio_unidad) {

                if (empty($item->ma_codigo)) {
                    $item->errores[] = 'El campo CÓDIGO PRODUCTO es requerido';
                } else {
                    $material = Material::find([
                        'codigo' => $item->ma_codigo,
                        'estado' => Material::_STATE_ENABLED,
                    ]);
                    if ($material->exist()) {
                        $item->id_material = $material->id;
                        $item->ma_nombre = $material->nombre;
                    } else {
                        $item->errores[] = 'No se reconoce el producto';
                    }
                }

                if (empty($item->guia_remision)) {
                    $item->errores[] = 'El campo # GUÍA REMISIÓN es requerido';
                }

                if ($item->cantidad_ingresar <= 0) {
                    $item->errores[] = 'El campo CANTIDAD A INGRESAR es requerido';
                }

                if ($item->precio_unidad <= 0) {
                    $item->errores[] = 'El campo PRECIO UNITARIO es requerido';
                }

                if (empty($item->fecha_ingreso)) {
                    $item->errores[] = 'El campo FECHA INGRESO es requerido';
                } else if (!Util::isDate($item->fecha_ingreso)) {
                    $item->errores[] = 'El campo FECHA INGRESO no tiene formato correcto (año-mes-dia) ej.: 2021-06-10';
                }

                $items[] = $item;
            }
        }

        return $items;
    }

}