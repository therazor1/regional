<?php namespace Controllers\admin;

use Inc\Auth;
use Inc\Export;
use Inc\Push;
use Inc\Req;
use Inc\Rsp;
use Inc\Util;
use Libs\Pixie\QB;
use Models\Client;
use Models\Log;
use Models\Notification;
use Models\Plan;
use Models\PlanReport;
use Models\Salida;
use Models\TypeSend;
use Models\User;

class notifications extends _controller
{
    const CAN_ALL = true;

    public function index(Req $req)
    {
        $fil = $req->fil();

        $qb = QB::table('notifications no');
        $qb->select(
            'no.*',
            "CONCAT(us.name,' ',us.surname) usuario",
            "CONCAT(op.name,' ',op.surname) operador",
        );
        $qb->leftJoin('type_sends ts', 'ts.id', '=', 'no.id_type_send');
        $qb->leftJoin('users us', 'us.id', '=', 'no.id_user');
        $qb->leftJoin('users op', 'op.id', '=', 'no.id_operator');
        $qb->where('no.state', '!=', Notification::_STATE_DELETED);

        if ($fil->query)
            /*Search*/
            $qb->whereLike('CONCAT(us.name,us.surname,no.title,no.body)', $fil->query);


        if ($fil->date_from && $fil->date_to)
            $qb->whereBetween('DATE(no.date_created)', $fil->date_from, $fil->date_to);

        if ($fil->order_col && $fil->order_dir)
            $qb->orderBy($fil->order_col, $fil->order_dir);


        return $fil->pager($qb, function ($o) use ($fil) {
            if ($fil->export) return [
                'ID'                      => $o->id,
                'Titulo'             => $o->title,
                'Contenido'           => $o->body,
                'Fecha'              => Util::humanDate($o->date_created)
            ];

            return $o;
        });
    }

    /**
     *se hace la peticion a este metodo desde el front
     */
    public function conductores(Req $req){
        $fil = $req->fil([

        ]);

        $qb = QB::table('users');
        $qb->select(
            'id',
            "CONCAT(name,' ',surname) name",
            "token_fcm"

        );
        $qb->where('id_role', User::ROLE_CONDUCTOR);

        if ($fil->query)
            $qb->whereLike('name', $fil->query);

        $items = [];
        foreach ($qb->get() as $o) {
            $items[] = $o;
        }

        return Rsp::items($items)
            ->set('total', $qb->count())
            ->set('sql', $qb->getSQL());
    }

    public function item($id)
    {
        $item = Notification::find($id);

        if ($item->exist()) {
            return rsp(true)->merge($item);
        } else {
            return rsp('No se reconoce');
        }
    }

    /**
     *Tambien es necesario para select en el front -> send push multiple users
     */
    public function preCreate(Req $req)
    {
        return Rsp::ok()
            //->set('id_users', $this->_id_users($req));
            ->set('id_users', $this->conductores($req));
    }

    //Aquí mandamos cuando presiono el btn "ENVIAR" desde el CMS
    public function create(Req $req)
    {
        //validar y capturar campos
        $data = $req->data([
            'id_users' => 'array',
            'title' => 'required',
            'body' => ['required' => 'Contenido'],
            //'type' => ['required|id' => 'Tipo'],
        ]);


        # extraer todos los users que tengan un token asignado
        $users = User::where('token_fcm', '!=', '')
            //whereIn -> $data->id_users: es un array[31, 43]
            ->whereIn('id', $data->id_users)
            ->get();

        /**
         *REGISTRAR PUSH EN DB
         */
        foreach ($users as $user) {
            $item = new Notification();
            $item->data('id_operator', Auth::id());
            $item->data('id_user', $user->id);
            $item->data('title', $data->title);
            $item->data('body', $data->body);
            $item->save();
        }

        $arr_users = array_chunk($users, 950); //partir el array en trozos de 950

        foreach ($arr_users as $_users) {
            $push = new Push();
            //le paso el body al push
            $push->body($data->body);
            $push->title($data->title);

            foreach ($_users as $_user) {
                $push->token($_user->token_fcm);
            }
            $push->send();
        }

        Log::addMe(20, 0, '', json_encode([
            'num_users' => count($users),
            'title' => $data->title,
            'body' => $data->body,
        ]));

        return Rsp::ok()
            ->set('msg', $push->status_message);
    }

    public function form(Req $req)
    {
        /*$plan_reports = QB::table('plan_reports pr')
            ->join('plans pl', 'pl.id', '=', 'pr.id_plan')
            ->join('company_divisions di', 'di.id', '=', 'pl.id_company_division')
            ->join('users us', 'us.id', '=', 'pl.id_representative')
            ->join('companies co', 'co.id', '=', 'pl.id_company')
            ->select(
                'pl.*',
                'pr.id pr_id',
                'pr.date_next pr_date_next',
                'pr.place_next pr_place_next',
                'pr.sent_yellow pr_sent_yellow',
                'pr.sent_red pr_sent_red',
                'di.name di_name',
                'us.id us_id',
                'us.name us_name',
                'us.surname us_surname',
                'us.phone us_phone',
                'us.lat us_lat',
                'us.lng us_lng',
                'co.id co_id',
                'co.name co_name',
                'di.id di_id',
                'di.name di_name'
            )
            ->where('pl.state', Plan::STATE_ACTIVE)
            ->where('pr.state', PlanReport::STATE_PENDING)
            ->orderBy('pr.sent_red', 'DESC')
            ->orderBy('pr.sent_yellow', 'DESC')
            ->orderBy('pl.org_date', 'ASC')
            ->get();*/

        /*return Rsp::ok()
            ->set('item', [
                'id_type_send' => '17',
            ])
            ->set('type_sends', TypeSend::all())
            //->set('plan_reports', $plan_reports)
            ->set('types', [
                pair(Notification::TYPE_SISMOS, 'Sismos'),
                pair(Notification::TYPE_REC, 'REC'),
                pair(Notification::TYPE_PLAN_DE_VIAJE, 'Plan de Viaje'),
                pair(Notification::TYPE_METEOROLOGY, 'Meteorología'),
                pair(Notification::TYPE_NOTICIAS, 'Noticias'),
            ]);*/


        $rsp = Rsp::ok();
        //======output======
       /* $rsp->set('conductores', User::where('id_role',User::ROLE_CONDUCTOR)->select('id',"concat(name,' ',surname) name")->get());*/

        $conductores = QB::table('users') //todos los conductores
        ->select(
            'id',
            "CONCAT(name,' ',surname) name"
        )
            ->where('id_role', User::ROLE_CONDUCTOR)
            ->get();
        $rsp->set('conductores', $conductores);

        return $rsp;

    }

    public function read(Req $req)
    {
        $data = $req->data([
            'ids' => 'required|array|min:1',
        ]);

        foreach ($data->ids as $id) {
            Notification::where('id', $id)->update(['state' => Notification::STATE_READED]);
        }

        return rsp(true);
    }

    public function remove(Req $req)
    {
        return Notification::deleteRSP($req->num('id'));
    }

}
