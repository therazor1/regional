<?php namespace Controllers\admin;

use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Inc\utils\ULog;
use Libs\Pixie\QB;

class logs extends _controller
{
    protected $no_see = ['index'];

    public function index(Req $req)
    {
        $fil = $req->fil([
            'name'        => $req->any('name'),
            'id_type_log' => $req->num('id_type_log'),
            'id_user'     => $req->num('id_user'),
            'type_user'   => $req->num('type_user'),
            'id_target'   => $req->num('id_target'),
            'target'      => $req->any('target'),
        ]);

        $qb = QB::table('logs lo');
        $qb->leftJoin('users us', 'us.id', '=', 'lo.id_user');
        $qb->leftJoin('type_users tu', 'tu.id', '=', 'us.id_type_user');
        $qb->leftJoin('roles ro', 'ro.id', '=', 'us.id_role');
        $qb->join('type_logs tl', 'tl.id', '=', 'lo.id_type_log');
        $qb->select(
            'lo.*',
            'us.name us_name',
            'tu.id tu_id',
            'tu.name tu_name',
            'ro.id ro_id',
            'ro.name ro_name',
            'tl.icon tl_icon',
            'tl.color tl_color',
            'tl.prefix tl_prefix',
            'tl.name tl_name',
            'tl.suffix tl_suffix');

        if ($fil->id)
            $qb->where('lo.id', $fil->id);

        if ($fil->type_user)
            $qb->where('us.type', $fil->type_user);

        if ($fil->id_user)
            $qb->where('lo.id_user', $fil->id_user);

        if ($fil->id_type_log)
            $qb->where('lo.id_type_log', $fil->id_type_log);

        if ($fil->target) {
            $qb->where(function ($q) use ($fil) {
                $q->where('lo.target', $fil->target);
                $q->orWhere('lo.parent', $fil->target);
            });
        }

        if ($fil->id_target) {
            $qb->where(function ($q) use ($fil) {
                $q->where('lo.id_target', $fil->id_target);
                $q->orWhere('lo.id_parent', $fil->id_target);
            });
        }

        if ($fil->query)
            $qb->whereLike('us.name', $fil->query);

        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(lo.date_created)', $fil->date_from, $fil->date_to);

        return $fil->pager($qb, function ($o) use ($fil) {
            $o = ULog::makeItem($o);



            if ($fil->export) return [
                'ID'    => $o->id,
                'Texto' => $o->text,
                'Fecha' => Util::humanDatetime($o->date_created),
            ];

            return $o;
        });
    }

    public function type_logs()
    {
        $items = QB::table('type_logs')
            ->select('id', "CONCAT(prefix,' ',name,' ',suffix) name")
            ->orderBy('name')
            ->get();
        return Rsp::items($items);
    }

    public function targets()
    {
        $items = [];

        foreach (ULog::$targets as $key => $val) {
            $items[] = par($key, $val);
        }

        return Rsp::items($items);
    }

}