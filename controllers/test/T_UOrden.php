<?php namespace Controllers\test;

use Inc\Rsp;
use Models\Orden;

class T_UOrden extends _controller
{

    public function exportar($id = '996')
    {
        $orden = Orden::find($id);
        return $orden->exportar();
    }

    public function generarAprovaciones($id = '2')
    {
        $orden = Orden::find($id);
        return $orden->generarAprobaciones();
    }

    public function enviarSiguienteAprobacion($id = '2')
    {
        return Orden::find($id)->enviarSiguienteAprobacion();
    }

    public function guardar($id = '1')
    {
        $orden = Orden::find($id);
        return $orden->guardar();
    }

    public function generarPDF($id = '1')
    {
        $orden = Orden::find($id);
        $pdf = $orden->guardar();
        $orden->update([
            'pdf' => $pdf,
        ]);
        return Rsp::ok('pdf asignado a la orden')
            ->set('pdf', $pdf);
    }

    public function guarderOrdenesAprobadasSinPDF()
    {

        /** @var Orden[] $ordenes */
        $ordenes = Orden::where('estado', Orden::ESTADO_APROBADA)
            ->where('pdf', '=', '')
            ->limit(30)
            ->get();

        foreach ($ordenes as $orden) {
            if ($pdf = $orden->guardar()) {
                $orden->update([
                    'pdf' => $pdf,
                ]);
            }
        }

        return $ordenes;
    }
}