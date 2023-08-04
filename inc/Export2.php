<?php namespace Inc;

use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Export2
{

    public static function exportPDF($columns, $rows, $filename)
    {
        $th_columns = '';
        $tr_rows = '';

        foreach ($columns as $col) {
            $th_columns .= '<th>' . $col . '</th>';
        }

        foreach ($rows as $row) {
            $tr_rows .= '<tr>';
            foreach ($row as $val) {
                $tr_rows .= '<td>' . $val . '</td>';
            }
            $tr_rows .= '</tr>';
        }

        $mpdf = new Mpdf();
        $mpdf->WriteHTML('
            <style>
                table {
                    border-collapse: collapse;
                    width: 100%;
                }
            
                table, th, td {
                    border: 1px solid black;
                }
            </style>
            
            <table>
                <thead>
                <tr>
                ' . $th_columns . '
                </tr>
                </thead>
                <tbody>
                ' . $tr_rows . '
                </tbody>
            </table>
        ');

        $mpdf->Output($filename . '.pdf', 'D');
    }

    public static function exportExcel($columns, $rows, $name = null)
    {
        array_unshift($rows, $columns); // agregar las columnad a los rows, pero al inicio

        if (empty($name)) {
            $name = 'export_' . date("d-m-Y_g-i-s_a");
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $i1 => $v1) {
            $v1 = array_values($v1);
            foreach ($v1 as $i2 => $v2) {
                $sheet->setCellValueByColumnAndRow($i2 + 1, $i1 + 1, $v2);
            }
        }

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $name . '.xlsx"');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public static function exportCSV($columns, $rows, $name = null)
    {
        array_unshift($rows, $columns); // agregar las columnad a los rows, pero al inicio

        if (empty($name)) {
            $name = 'export_' . date("d-m-Y_g-i-s_a");
        }

        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename=" . $name . ".csv");
        header("Content-Transfer-Encoding: binary");

        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($rows)));
        foreach ($rows as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        echo mb_convert_encoding(ob_get_clean(), 'UCS-2LE', 'UTF-8');;

        exit;
    }

    # EXPORTAR A UN FORMATO
    public static function any($format, $rows, $filename)
    {
        if (empty($rows)) {
            $columns = [];
        } else {
            $columns = array_keys($rows[0]);
        }

        switch ($format) {
            case 'pdf':
                self::exportPDF($columns, $rows, $filename);
                break;
            case 'csv':
                self::exportCSV($columns, $rows, $filename);
                break;
            case 'xslx':
            default:
                self::exportExcel($columns, $rows, $filename);
                break;
        }
    }

}