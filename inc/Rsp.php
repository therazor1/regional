<?php namespace Inc;

use stdClass;

class Rsp extends stdClass
{

    public $ok = false;
    public $msg = '';

    /**
     * @param string $k
     * @param mixed $v
     * @return Rsp
     */
    function set($k, $v)
    {
        $this->$k = $v;
        return $this;
    }

    /**
     * @param mixed $item
     * @return Rsp
     */
    function setItem($item)
    {
        $this->set('item', $item);
        return $this;
    }

    /**
     * Combinar un objeto / array
     * @param array|object $obj_or_arr
     * @return Rsp
     */
    function merge($obj_or_arr)
    {
        //$items = (array) $obj_or_arr;
        foreach ($obj_or_arr as $k => $v) {
            $this->set($k, $v);
        }
        return $this;
    }

    /**
     * @param string $field
     * @param string|null $message : si envia null, se definira un mensaje
     * @return $this
     */
    function error($field, $message = null)
    {
        $this->errors[$field] = $message ?: 'Este campo es requerido';
        $this->ok = false;
        return $this;
    }

    function msg($msg)
    {
        $this->msg = $msg;
        return $this;
    }

    function hide($key)
    {
        unset($this->$key);
        return $this;
    }

    public function __toString()
    {
        return json_encode($this);
    }

    public function done()
    {
        header('Content-Type: application/json');
        exit(json_encode($this));
    }

    /* HELPERS */
    static function e404()
    {
        return rsp('No existe');
    }

    static function e403()
    {
        return rsp('No te pertenece');
    }

    static function e500()
    {
        return rsp('Error interno');
    }

    static function ok($msg = null)
    {
        return rsp(true, $msg);
    }

    static function okSaved()
    {
        return self::ok('Guardado correctamente');
    }

    static function okRemoved()
    {
        return self::ok('Eliminado correctamente');
    }

    static function item($item)
    {
        return self::ok()->setItem($item);
    }

    static function items($items)
    {
        return self::ok()->set('items', $items);
    }

}