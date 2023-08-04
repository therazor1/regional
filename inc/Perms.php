<?php namespace Inc;

use Controllers\admin\_controller;
use Libs\Pixie\QB;
use stdClass;

class Perms
{

    /* @var Perms */
    private static $ins;

    public static function ins()
    {
        if (!static::$ins) static::$ins = new static;
        return static::$ins;
    }

    public static function newIns()
    {
        static::$ins = new static;
        return static::$ins;
    }

    private $home = 'home';
    private $menu = [];
    private $current_module = null;

    public function __construct()
    {
        $this->loadPerms();
    }

    public function loadPerms()
    {
        if (Auth::logged()) {

            $menus = QB::table('perms pe')
                ->select(
                    'mo.id',
                    'mo.name',
                    'mo.url',
                    'mo.icon',
                    'mo.section',
                    'pe.see',
                    'mo.id_parent',
                    'pe.edit'
                )
                ->join('modules mo', 'mo.id', '=', 'pe.id_module')
                ->where('pe.id_role', Auth::user()->id_role)
                ->where('mo.state', 1)
                ->orderBy('mo.sort')
                ->get();

            if ($menus) {
                foreach ($menus as $menu) {
                    $this->menu[] = $menu;
                    if ($menu->id == Auth::user()->ro_id_module)
                        $this->home = $menu->url;
                    // Modulo Actual
                    if ($menu->url == _controller::$module) {
                        $this->current_module = $menu;
                    }
                }
            }
        }
    }

    /**
     * @return stdClass|null
     */
    public static function current()
    {
        return self::ins()->current_module;
    }

    /**
     * @param $module
     * @return stdClass|null
     */
    public static function getItem($module)
    {
        foreach (self::ins()->menu as $item) {
            if ($item->url == $module) {
                return $item;
            }
        }
        return null;
        //return isset(self::ins()->menu[$module]) ? self::ins()->menu[$module] : null;
    }

    /***
     * Saber si tiene permiso de lectura
     * @param array $modules : si envia NULL se refiere al modulo actual
     * @return bool
     */
    public static function see($modules = [])
    {
        # si es root tiene acceso a todos
        if (Auth::root()) return true;
        # verificar si tiene acceso al modulo actual
        $mod = self::current();
        if ($mod && $mod->see == 1) return true;

        # si paso otros modulos, tambien lo verificamos
        if ($modules) {
            foreach (self::ins()->menu as $item) {
                if ($item->url && in_array($item->url, $modules)) {
                    if ($item->see == 1) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /***
     * Saber si tiene permiso de escritura
     * @param array $modules revisar otros modulos
     * @return bool
     */
    public static function can($modules = [])
    {
        # si es root tiene acceso a todos
        if (Auth::root()) return true;
        # verificar si tiene acceso al modulo actual
        $mod = self::current();
        if ($mod && $mod->edit == 1) return true;

        # si paso otros modulos, tambien lo verificamos
        if ($modules) {
            foreach (self::ins()->menu as $item) {
                if ($item->url && in_array($item->url, $modules)) {
                    if ($item->edit == 1) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /* @return string : url de inicio */
    public static function home()
    {
        return self::ins()->home;
    }

    /* @return array : Menu principal */
    public static function menu()
    {
        $items = [];
        foreach (self::ins()->menu as $item) {
            if ($item->see == 1) {
                $items[$item->id] = (object)[
                    'id'        => $item->id,
                    'id_parent' => $item->id_parent,
                    'title'     => $item->name,
                    'type'      => $item->section ? 'group' : 'item',
                    'icon'      => $item->icon,
                    'see'       => $item->see == '1',
                    'edit'      => $item->edit == '1',
                    'url'       => $item->url ? '/' . $item->url : '',
                ];
            }
        }
        return $items;
    }

    public static function menuSorted()
    {
        return Util::ordMenu(self::menu());
    }
}
