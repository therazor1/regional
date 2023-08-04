<?php namespace Controllers\test;

use Inc\database\QB;

class T_QB extends _controller
{

    public function pruebas()
    {
        #return DB::query("select nombre from distritos limit 10")->get();

        return QB::table('arear ar')
            ->select(
                'ar.*',
                'ob.nombre ob_nombre')
            ->join('obras ob', 'ob.id', '=', 'ar.id_obra')
            ->where('ar.nombre', '=', '"mm""')
            ->limit(10)
            #->getSQL()
            ->showSQL()#->first()
            ;
    }

}