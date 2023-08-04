<?php namespace Inc;

use Libs\Pixie\QB;
use stdClass;

class Fil extends stdClass
{
    public $start;
    public $length;
    public $order_col;
    public $order_dir;
    public $date_from;
    public $date_to;
    public $id;
    public $query;
    public $state;
    public $export;
    public $value;

    public function __construct($items = null)
    {
        if ($items) {
            $this->merge($items);
        }
    }

    /**
     * @param string $k
     * @param mixed $v
     * @return Fil
     */
    function set($k, $v)
    {
        $this->$k = $v;
        return $this;
    }

    /**
     * Combinar un objeto / array
     * @param array|object $obj_or_arr
     * @return Fil
     */
    function merge($obj_or_arr)
    {
        foreach ($obj_or_arr as $k => $v) {
            $this->set($k, $v);
        }
        return $this;
    }

    function pager(QB $qb, $onItem = null, string $title = null)
    {

        if ($this->order_col && $this->order_dir)
            $qb->orderBy($this->order_col, $this->order_dir);

        if (!$this->export) {
            $qb->offset($this->page * $this->limit);
            $qb->limit($this->limit);
        }

        $items = [];

        if (is_callable($onItem)) {

            foreach ($qb->get() as $o) {
                $items[] = $onItem($o);
            }

        } else {
            $items = $qb->get();
        }

        if ($this->export) {
            if (empty($title))
                $title = Perms::current() ? Perms::current()->name : stg('brand');

            Export::any($this->export, $items, $title);
        }

        return Rsp::items($items)
            ->set('total', $qb->count())
            ->set('sql', $qb->getSQL());
    }
}