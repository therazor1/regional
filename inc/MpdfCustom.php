<?php namespace Inc;

use Mpdf\Mpdf;

class MpdfCustom extends Mpdf
{

    # la finalidad de sobreescribir este metodo es que no permite agrandar el texto de la marca de agua
    # solo permite hasta cierto limite, y al enviarle el ->SetWatermarkText no admite tamaño de fuente
    # entonces la unica solucion es extender la clase
    function watermark($texte, $angle = 45, $fontsize = 96, $alpha = 0.2)
    {
        $x = 13;
        $y = 30;
        $this->SetFont('Arial', size: 154, style: 'B');
        $this->SetTextColor('#000000');
        $this->SetAlpha(0.2);
        $this->Rotate(-58, $x, $y);
        $this->Text($x, $y, $texte);
        $this->Rotate(0);
    }

}