<?php namespace Controllers\test;

use Libs\Pixie\QB;
use Models\FlujoAprobacion;
use Models\Material;
use Models\Orden;

class T_Test extends _controller
{

    public function siguienteCorrelativoNum()
    {
        return Orden::siguienteCorrelativoNum(true);
    }

    public function siguienteCodigoProducto()
    {
        $data = (object)[
            'id_material'   => 0,
            'id_subfamilia' => 38,
        ];
        return Material::codigoProductoDisponible($data->id_material, $data->id_subfamilia);
    }

    public function replicarFlujoAprobacionesObras()
    {
        $original_flujos = FlujoAprobacion::byObra(0);

        # obras sin flujos
        $obras_sin_flujo = QB::table('obras ob')
            ->select([
                'ob.id',
                'ob.nombre',
            ])
            ->leftJoin('flujo_aprobaciones fa', function ($t) {
                $t->on('fa.id_obra', '=', 'ob.id');
                $t->on('fa.estado', '=', FlujoAprobacion::_STATE_ENABLED);
            })
            ->whereNull('fa.id')
            ->get();

        foreach ($obras_sin_flujo as $ob) {
            # replicar los flujos para las obras
            foreach ($original_flujos as $fa) {
                FlujoAprobacion::insert([
                    'id_obra' => $ob->id,
                    'nombre'  => $fa->nombre,
                    'monto'   => $fa->monto,
                ]);
            }
        }

        return $obras_sin_flujo;
    }

}