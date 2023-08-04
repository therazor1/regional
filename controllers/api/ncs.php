<?php namespace Controllers\api;

use Inc\Rsp;
use Libs\Pixie\QB;
use Models\Management;
use Models\Module;
use Models\Reniec;
use Models\User;

class ncs extends _controller
{

    public static function index()
    {

        $managements = Management::all();
        return $managements;

    }

    public function crearModulo()
    {
        $url = "approval_leaders";
        $name = "Aprobación de líderes";

        $item = Module::find('url', $url);

        if ($item->exist()) {
            return Rsp::ok('Modulo ya existe')->setItem($item);
        } else {
            $id = QB::table('modules')->insert([
                'id_parent'    => '',
                'id_type_user' => User::TYPE_OPERATOR,
                'sort'         => '1',
                'name'         => $name,
                'url'          => $url,
                'icon'         => '',
                'root'         => '0',
            ]);

            return Rsp::ok('Se creo correctamente')->set('id', $id);
        }

    }
}



