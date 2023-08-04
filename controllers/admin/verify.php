<?php namespace Controllers\admin;

use Inc\Auth;
use Inc\Perms;
use Inc\Rsp;
use Inc\STG;
use Libs\Pixie\QB;

class verify extends _controller
{
    const AUTH_REQUIRED = false;

    public function index()
    {
        $userData = null;
        $me = Auth::user();

        $menu = Perms::menuSorted();
        $url_home = '/' . Perms::home();

        if ($me) {
            $shortcuts = array_column(QB::table('user_shortcuts')->where('id_user', $me->id)->get(), 'id_module');

            $userData = [
                'id'           => $me->id,
                'id_type_user' => $me->id_type_user,
                'name'         => $me->name,
                'surname'      => $me->surname,
                'displayName'  => $me->name . ' ' . $me->surname[0] . '.',
                'pic'          => $me->pic,
                'email'        => $me->email,
                'ro_id'        => $me->id_role,
                'ro_name'      => $me->ro_name,
                'shortcuts'    => $shortcuts,
                'settings'     => [
                    'layout' => [
                        'config' => [
                            'navbar' => [
                                'folded' => $me->ro_menu_collapsed == '1',
                            ],
                            'footer' => [
                                'display' => false,
                                'style'   => 'static',
                            ],
                        ],
                    ],
                    'theme'  => [
                        'main' => 'default',
                    ],
                ],
            ];
        }

        return Rsp::ok()
            ->set('token', Auth::$token)
            ->set('user', [
                'role'     => $me ? 'admin' : null,
                'data'     => $userData,
                'url_home' => $url_home,
                'base_dir' => $me ? $me->baseDir() : '',
                'menu'     => $menu,
                'settings' => STG::all(),
            ]);
    }

}
