<?php namespace Inc\utils;

use Inc\Util;
use Libs\Pixie\QB;
use Models\User;

class ULog
{
    static $targets = [
        'notifications'   => 'notificación',
        'modules'         => 'módulo',
        'roles'           => 'perfil',
        'settings'        => 'ajustes',
        'users'           => 'usuario',
        'frente_trabajos' => 'frente de trabajo',
        'licitaciones'    => 'licitación',
        'proveedores'     => 'proveedor',
        'ordenes'         => 'orden',
        'materiales'      => 'producto',
        'legal'           => 'términos y condiciones',
        'user_obras'      => 'obra de usuario',
    ];

    static function makeItem($o)
    {
        $o->user_link = '';

        switch ($o->tu_id) {
            case User::TYPE_OPERATOR:
                $o->user_link = '/usuarios/' . $o->id_user;
                break;
            case User::TYPE_PROVIDER:
                $o->user_link = '/proveedores';
                break;
        }

        $target_item = ULog::getTargetItem($o->target, $o->id_target);

        $o->type_user = ($o->id_user > 0 ? $o->ro_name : 'Sistema');
        $o->user_name = $o->us_name;
        $o->type_log_prefix = $o->tl_prefix;
        $o->type_log_name = $o->tl_name;
        $o->type_log_suffix = $o->tl_suffix;
        $o->target_name = (@ULog::$targets[$o->target] ?: $o->target);
        $o->target_item_link = $target_item['link'];
        $o->target_item_name = $target_item['name'];
        $o->ago = Util::ago($o->date_created);

        $text = $o->type_user;
        $text .= ' ' . $o->user_name;
        $text .= ' ' . $o->type_log_prefix;
        $text .= ' ' . $o->type_log_name;
        $text .= ' ' . $o->type_log_suffix;
        $text .= ' ' . $o->target_name;
        $text .= ' ' . $o->target_item_name;

        if ($o->data) {
            $o->data_json = self::parseJson($o->data);
        }

        $o->text = $text;
        return $o;
    }

    static function parseJson($string)
    {
        $obj = json_decode($string);
        if ((json_last_error() != JSON_ERROR_NONE)) return null;
        return $obj;
    }

    static function getTargetItem($target, $id_target)
    {
        if ($id_target && $o = QB::table($target)->where('id', $id_target)->first()) {
            switch ($target) {
                case 'notifications':
                    return self::targetItem('/notifications', Util::htmlSummary($o->body));
                case 'proveedores':
                    return self::targetItem('/proveedores/' . $o->id, $o->nombre);
                case 'ordenes':
                    return self::targetItem('/ordenes', $o->correlativo);
                case 'users':
                    return self::targetItem('/usuarios/' . $o->id, $o->name);
                default:
                    if (isset($o->name)) {
                        return self::targetItem('/' . $target, $o->name);
                    } else if (isset($o->nombre)) {
                        return self::targetItem('/' . $target, $o->nombre);
                    } else {
                        return self::targetItem('/' . $target, '#' . $id_target);
                    }
            }
        } else {
            return self::targetItem('', '');
        }
    }

    static function targetItem($link, $name)
    {
        return [
            'link' => $link,
            'name' => $name,
        ];
    }

}