<?php namespace Controllers\admin;

use Inc\Date;
use Inc\Export;
use Inc\Req;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Session;

class sessions extends _controller
{

    public function index(Req $req)
    {
        $fil = $req->fil([
            'id_user'   => $req->num('id_user'),
            'order_col' => $req->any('order_col', 'date_expiration'),
            'order_dir' => $req->any('order_dir', 'DESC'),
        ]);

        $qb = QB::table('sessions se');
        $qb->join('users us', 'us.id', '=', 'se.id_user');
        $qb->join('type_users tu', 'tu.id', '=', 'us.id_type_user');
        $qb->select(
            'se.*',
            'us.name us_name',
            'us.surname us_surname',
            'us.pic us_pic',
            'tu.name tu_name'
        );

        if ($fil->id_user)
            $qb->where('se.id_user', '=', $fil->id_user);

        if ($fil->query)
            $qb->where('CONCAT(se.platform,se.device_brand,se.device_model,us.name,us.surname)',
                'LIKE', '%' . str_replace(' ', '%', $fil->query) . '%');

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(se.date_created)', $fil->date_from, $fil->date_to);

        if ($fil->order_col && $fil->order_dir)
            $qb->orderBy($fil->order_col, $fil->order_dir);

        if (!$fil->export) {
            $qb->offset($fil->page * $fil->limit);
            $qb->limit($fil->limit);
        }

        $items = [];

        foreach ($qb->get() as $o) {

            $o->date_expiration_ago = $o->date_expiration
                ? Date::ins($o->date_expiration)->ago()
                : '';

            if ($fil->export) {
                $items[] = [
                    'ID'         => $o->id,
                    'Persona'    => $o->us_name . ' ' . $o->us_surname,
                    'Plataforma' => $o->platform,
                    'Fecha'      => Util::humanDatetime($o->date_created),
                ];
            } else {
                $items[] = $o;
            }
        }

        if ($fil->export)
            Export::any($fil->export, $items, $this->title());

        return rsp(true)
            ->set('items', $items)
            ->set('total', $qb->count())
            ->set('sql', $qb->getSQL());
    }

    public function remove(Req $req)
    {
        return Session::deleteRSP($req->id());
    }
}