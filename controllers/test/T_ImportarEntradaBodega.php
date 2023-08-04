<?php namespace Controllers\test;

use Inc\utils\UEntradaBodega;

class T_ImportarEntradaBodega extends _controller
{

    public function verificar()
    {
        $file_path = _PATH_ . '/assets/files/formatos/entrada_bodega.xlsx';

        return UEntradaBodega::verificar($file_path);
    }

}