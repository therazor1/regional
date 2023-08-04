<?php namespace Inc;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Export
{
    const FORMAT_XLSX = 'xlsx';
    const FORMAT_PDF = 'pdf';
    const FORMAT_CSV = 'csv';
    const FORMAT_HTML = 'html';

    const ACTION_DOWNLOAD = 'download';
    const ACTION_SHOW = 'show';
    const ACTION_SAVE = 'save';

    private $columns = [];
    private $rows = [];
    private $name;
    private $folder; # para guardar
    private $prefix;
    private $format = self::FORMAT_XLSX;
    private $action = self::ACTION_DOWNLOAD;

    private $extensions = [
        self::FORMAT_XLSX => 'xlsx',
        self::FORMAT_PDF  => 'pdf',
        self::FORMAT_CSV  => 'csv',
        self::FORMAT_HTML => 'html',
    ];

    public function __construct()
    {
    }

    public function columns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function rows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    public function rowsColumns($rows)
    {
        $this->rows($rows);
        if (empty($rows)) {
            $this->columns([]);
        } else {
            /*aquí error export*/
            $this->columns(array_keys($rows[0]));
        }
        return $this;
    }

    public function folder($folder)
    {
        $this->folder = $folder;
        return $this;
    }

    public function prefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function format($format)
    {
        $this->format = $format;
        return $this;
    }

    public function action($action)
    {
        $this->action = $action;
        return $this;
    }

    # acciones
    public function download()
    {
        $this->action(self::ACTION_DOWNLOAD);
        return $this->go();
    }

    public function show()
    {
        $this->action(self::ACTION_SHOW);
        return $this->go();
    }

    public function save()
    {
        $this->action(self::ACTION_SAVE);
        return $this->go();
    }

    # utilidades
    private function getFolder()
    {
        return $this->folder ?: 'exports';
    }

    private function getName()
    {
        return ($this->prefix ? $this->prefix . '_' : '')
            . ($this->name ? $this->name : 'export_' . date('Ymd_His') . '_' . uniqid());
    }

    private function _generateName()
    {
        $name = $this->getName();
        $ext = isset($this->extensions[$this->format]) ? $this->extensions[$this->format] : 'exp';
        return $name . '.' . $ext;
    }

    public function go()
    {
        switch ($this->format) {
            case self::FORMAT_XLSX:
                return $this->_goXLSX();
            case self::FORMAT_PDF:
                return $this->_goPDF();
            case self::FORMAT_CSV:
                return $this->_goCSV();
            case self::FORMAT_HTML:
                return $this->_goHTML();
            default:
                return '';
        }
    }

    private function _buildSpreadsheet()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->getProperties()
            ->setTitle($this->getName())
            ->setCompany('Focus IT')
            ->setCreator('Alvaro Chachapoyas');

        $sheet->setTitle('Hoja 1');

        # columnas
        if ($this->columns) {
            foreach ($this->columns as $i => $column) {
                $sheet->setCellValueByColumnAndRow($i + 1, 1, $column);
            }

            $sheet->freezePane('A2');
        }

        # filas
        foreach ($this->rows as $i1 => $v1) {
            $v1 = array_values($v1);
            foreach ($v1 as $i2 => $v2) {
                $sheet->setCellValueByColumnAndRow($i2 + 1, $i1 + 2, $v2);
            }
        }

        # las columnas con auto automatico
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function _write($writer, $content_type = null)
    {
        $pic = $this->_generateName();

        if ($this->action == self::ACTION_SAVE) {

            $folder = '/' . $this->getFolder();
            $pic = '/' . $pic;
            $path = upl($folder) . $pic;
            $writer->save($path);

            return $folder . $pic;

        } else if ($this->action == self::ACTION_SHOW) {
            if ($content_type) {
                header('Content-type: ' . $content_type);
            }
            $writer->save('php://output');
            exit;

        } else {
            if ($content_type) {
                header('Content-type: ' . $content_type);
                header('Content-Disposition: attachment; filename="' . $pic . '"');
            }
            $writer->save('php://output');
            exit;
        }
    }

    private function _goXLSX()
    {
        $spreadsheet = $this->_buildSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        return $this->_write($writer, 'application/vnd.ms-excel');
    }

    private function _goPDF()
    {
        $spreadsheet = $this->_buildSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)
            ->getBorders()->getAllBorders()->setBorderStyle(true);

        $sheet->getPageMargins()
            ->setTop(0.1)
            ->setRight(0.1)
            ->setBottom(0.1)
            ->setLeft(0.1);

        $writer = new Mpdf($spreadsheet);

        return $this->_write($writer, 'application/pdf');
    }

    private function _goCSV()
    {
        $spreadsheet = $this->_buildSpreadsheet();
        $writer = new Csv($spreadsheet);
        return $this->_write($writer, 'text/csv');
    }

    private function _goHTML()
    {
        $spreadsheet = $this->_buildSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);

        $sheet->getPageMargins()
            ->setTop(0.02)
            ->setRight(0.02)
            ->setBottom(0.02)
            ->setLeft(0.02);

        $style_all = $sheet->getStyle('A1:' . $highestColumn . $highestRow);
        $style_all->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $style_all->getFont()->setName('Arial');

        $writer = new Html($spreadsheet);

        return $this->_write($writer, 'text/html');
    }

    # HELPERS

    public static function xlsx()
    {
        $export = new Export();
        $export->format(self::FORMAT_XLSX);
        return $export;
    }

    public static function pdf()
    {
        $export = new Export();
        $export->format(self::FORMAT_PDF);
        return $export;
    }

    public static function csv()
    {
        $export = new Export();
        $export->format(self::FORMAT_CSV);
        return $export;
    }

    public static function html()
    {
        $export = new Export();
        $export->format(self::FORMAT_HTML);
        return $export;
    }

    # para cualquier formato
    public static function any($format, $rows, $filename)
    {
        $export = new Export();
        $export->format($format);
        $export->rowsColumns($rows);
        $export->name($filename);
        return $export->download();
    }

}