<?php namespace Controllers\admin;

use Inc\Auth;
use Inc\Date;
use Inc\Req;
use Inc\Rsp;
use Libs\Pixie\QB;
use Libs\Pixie\Raw;
use Models\Notificacion;

class live extends _controller
{
    const UPDATE_SESSION_EXPIRATION = false;

    public function index()
    {
        $notificaciones = QB::table('notificaciones')
            ->where('id_user', Auth::id())
            ->where('estado', '!=', Notificacion::ESTADO_ELIMINADO)
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get();

        foreach ($notificaciones as $o) {
            $o->ago = Date::ins($o->fecha_creado)->ago();
        }

        # Marcar como entregadas las pendientes
        if ($notificaciones) {
            Notificacion::where('id_user', Auth::id())
                ->where('estado', Notificacion::ESTADO_PENDIENTE)
                ->update([
                    'estado'          => Notificacion::ESTADO_ENTREGADO,
                    'fecha_entregado' => Raw::now(),
                ]);
        }

        return Rsp::ok()->set('notificaciones', $notificaciones)
            ->set('current_datetime', Date::ins()->format())
            ->set('session_date_expiration', Auth::user()->se_date_expiration);
    }

    public function leerNotificaciones(Req $req)
    {
        $data = $req->data([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|id',
        ]);

        foreach ($data->ids as $id) {
            Notificacion::id($id)->update([
                'estado'      => Notificacion::ESTADO_LEIDO,
                'fecha_leido' => Raw::now(),
            ]);
        }

        return Rsp::ok();
    }

    public function eliminarNotificacion(Req $req)
    {
        $id = $req->requiredId();
        $notificacion = Notificacion::find($id);

        if (!$notificacion->exist()) {
            return Rsp::ok();

        } else {

            $notificacion->data('estado', Notificacion::ESTADO_ELIMINADO);
            $notificacion->data('fecha_eliminado', Raw::now());

            return $notificacion->saveRSP();
        }
    }

}