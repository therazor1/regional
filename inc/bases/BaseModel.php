<?php namespace Inc\Bases;

use Inc\Auth;
use Inc\Pic;
use Inc\Rsp;
use Libs\Pixie\QB;
use Libs\Pixie\Raw;
use Models\Log;
use Models\State;
use stdClass;

abstract class BaseModel
{
    const ID = 'id';
    const DATE_CREATED = 'date_created';
    const DATE_UPDATED = 'date_updated';
    const DATE_DELETED = 'date_deleted';
    const STATE = 'state'; # dejar en null para desactivar el auto estado

    const ORDER_BY = null;

    const _STATE_DELETED = '0'; # eliminado
    const _STATE_ENABLED = '1'; # habilitado
    const _STATE_DISABLED = '2'; # desactivado

    const COLOR_ROJO='#FF1300';
    const COLOR_AMARILLO='#FFC300';
    const COLOR_VERDE='#2ECC71';

    protected $table = null;

    protected $hidden = [];

    protected $datas = []; // Datas para agregar o insertar

    /** @var Pic[] */
    protected $data_pics = []; // Datas de imagenes, se sube luego de guardar save()

    protected $data_changed = []; // Datos que fueron cambiados

    public function __construct()
    {
        // Asignar tabla si no fue asignada manual
        if (is_null($this->table)) {
            $this->table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', real_class(static::class))) . 's';
        }
    }

    /**
     * @param array $columns
     * @return static[]
     */
    public static function all($columns = ['*'])
    {
        $ins = new static;
        $qb = $ins->QB()
            ->select(is_array($columns) ? $columns : func_get_args())
            ->asObject(static::class);
        if (!is_null(static::STATE)) {
            $qb = $qb->where($ins->table . '.' . static::STATE, '!=', static::_STATE_DELETED);
        }
        if (!is_null(static::ORDER_BY)) {
            $qb = $qb->orderBy($ins->table . '.' . static::ORDER_BY);
        }
        return $qb->get();
    }

    /**
     * @param array $columns
     * @return static[]
     */
    public static function allEnabled($columns = ['*'])
    {
        $ins = new static;
        $qb = $ins->QB()
            ->select(is_array($columns) ? $columns : func_get_args())
            ->asObject(static::class);
        $qb = $qb->where($ins->table . '.' . static::STATE, '=', static::_STATE_ENABLED);
        if (!is_null(static::ORDER_BY)) {
            $qb = $qb->orderBy($ins->table . '.' . static::ORDER_BY);
        }
        return $qb->get();
    }

    /**
     * @return int
     */
    public static function count()
    {
        $ins = new static;
        return $ins->QB()->count();
    }

    /**
     * obtener un array de objetos
     * @param $sql
     * @return static[]
     */
    public static function query($sql)
    {
        return QB::query($sql)->asObject(static::class)->get();
    }

    /**
     * obtener el primer objecto de una consulta,
     * devuelve siempre una instancia, verificar con ->exist()
     * @param $sql
     * @return static
     */
    public static function queryFirst($sql)
    {
        return QB::query($sql)->asObject(static::class)->first() ?: (new static);
    }

    /**
     * Casos de uso
     *
     * model::find('foo')
     * model::find('foo','var')
     * model::find('foo','!=','var')
     * Model::find([
     *   'id'   => '2',
     *   'name' => ['LIKE', '%pt%'],
     * ])
     * Model::find([
     *   'email' => 'demo@demo.com',
     *   'state' => ['!=', '1'],
     * ])
     *
     * @param string|array $col
     * @param null|string $operator
     * @param null|string $val
     * @return static
     */
    public static function find($col, $operator = null, $val = null)
    {
        $new = (new static);
        $builder = $new->QB();
        $builder->asObject(static::class);
        if (is_array($col)) {
            foreach ($col as $k => $v) {
                if (is_array($v)) {
                    $builder->where($k, $v[0], $v[1]);
                } else {
                    $builder->where($k, '=', $v);
                }
            }

        } else if (is_null($val) && is_null($operator)) {
            $builder->where(static::ID, '=', $col);

        } else if (is_null($val)) {
            $builder->where($col, '=', $operator);

        } else {
            $builder->where($col, $operator, $val);
        }

        $item = $builder->first();
        if ($item) {
            if (!empty($new->hidden)) {
                foreach ($new->hidden as $attr) {
                    $item->$attr = '';
                }
            }
            return $item;
        } else {
            return $new;
        }
    }

    /**
     * @param $id
     * @return static|null
     */
    public static function findOrNull($id)
    {
        if ($id > 0) {
            $new = self::find($id);
            if ($new->exist()) {
                return $new;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @return QB
     */
    public static function where($key, $operator = null, $value = null)
    {
        $ins = new static;
        $qb = $ins->QB()->where($key, $operator, $value);
        $qb->asObject(static::class);
        return $qb;
    }

    /**
     * @return QB
     */
    public static function enabled()
    {
        $ins = new static;
        $qb = $ins->QB()->where(static::STATE, '=', static::_STATE_ENABLED);
        $qb->asObject(static::class);
        return $qb;
    }

    /**
     * @return QB
     */
    public static function QBuilder()
    {
        return (new static)->QB();
    }

    public static function insert($datas)
    {
        return (new static)->create($datas);
    }

    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * @param string $k
     * @param mixed $v
     * @param bool $keep_changes Si debemos considerar los cambios realizados para los logs
     * @return static
     */
    public function data($k, $v, $keep_changes = true)
    {
        $this->datas["`$k`"] = $v;

        if ($keep_changes) {
            if (isset($this->$k) && $this->$k != $v) {
                $this->data_changed[$k] = [
                    'old' => $this->$k,
                    'new' => $v,
                ];
            }
        }

        $this->$k = $v;
        return $this;
    }

    # obtener los datos cambiados
    public function dataChanged()
    {
        return $this->data_changed;
    }

    /**
     * @param null|array $merge_data_log
     * @return false|string
     */
    public function dataChangedJSON($merge_data_log = null)
    {

        return json_encode(is_null($merge_data_log)
            ? $this->dataChanged()
            : array_merge($this->dataChanged(), $merge_data_log));
    }

    /**
     * @param string $column columna
     * @param string|null $file_key file key opcional, se tomara la columna si no se pasa
     * @return Pic
     */
    public function dataPic(string $column, ?string $file_key = null): Pic
    {
        $pic = Pic::img($file_key ?? $column);
        $this->data_pics[$column] = $pic;
        return $pic;
    }

    /**
     * @param string $column columna
     * @param string|null $file_key file key opcional, se tomara la columna si no se pasa
     * @return Pic
     */
    public function dataFile(string $column, ?string $file_key = null): Pic
    {
        $pic = Pic::file($file_key ?? $column);
        $this->data_pics[$column] = $pic;
        return $pic;
    }

    /**
     * @param array|stdClass|null $datas
     * @return static
     */
    public function datas($datas = null)
    {
        if ($datas != null) {
            foreach ($datas as $k => $v) {
                $this->data($k, $v);
            }
        }
        return $this;
    }

    private function flush()
    {
        $this->datas = [];
        $this->data_pics = [];
    }

    /**
     * @param array|stdClass|null $datas
     * @return bool
     */
    public function update($datas = null)
    {
        if ($this->exist()) {
            $this->datas($datas);

            if ($this->datas) {
                if (static::DATE_UPDATED) {
                    $this->data(static::DATE_UPDATED, Raw::now(), false);
                }
                $result = $this->QB()
                    ->where(static::ID, '=', $this->getID())
                    ->update($this->datas);

                if ($result) {
                    $this->savePics();
                }

                return $result;
            }
        }
        return false;
    }

    public string $last_error = '';

    /**
     * @param array $datas
     * @return array|bool|string
     */
    public function create($datas = null)
    {
        $this->datas($datas);

        if (!empty($this->datas)) {
            $qb = $this->QB();
            $id = $qb->insert($this->datas);
            if ($id) {
                $attr_id = static::ID;
                $this->$attr_id = $id;

                $this->savePics();

                return $id;
            } else {
                $this->last_error = $qb->last_error;
            }
        }
        return false;
    }

    /**
     * Crear o actualizar corto
     * @param array|null $datas
     * @return bool|string
     */
    public function save($datas = null)
    {
        if ($this->exist()) {
            return $this->update($datas);
        } else {
            # no llenar el estado habilitado si no lo manejamos o bien si ya fue definido manualmente
            if (!is_null(static::STATE) && !isset($this->datas["`" . static::STATE . "`"])) {
                $this->data(static::STATE, static::_STATE_ENABLED, false);
            }
            return $this->create($datas);
        }
    }

    /**
     * Subir las pics si lo tiene
     */
    public function savePics()
    {
        if ($this->data_pics) {
            foreach ($this->data_pics as $column => $insPic) {
                $insPic->db($this->table, $column, $this->getID());
            }
        }
    }

    public function saveRSP($datas = null, $id_type_log = 0, $log_data = null, $log_id_parent = 0, $log_parent = '')
    {
        if ($this->exist()) {
            if ($this->update($datas)) {
                $data_changed = $this->dataChanged();
                Log::addMe($id_type_log ?: Log::UPDATE, $this->getID(),
                    $this->table, is_null($log_data)
                        ? $data_changed
                            ? json_encode($this->dataChanged())
                            : ''
                        : $log_data,
                    $log_id_parent, $log_parent);
                return Rsp::okSaved()
                    ->set('id', $this->getID())
                    ->set('is_new', false);
            } else {
                return Rsp::e500();
            }
        } else {
            if (!is_null(static::STATE) && !isset($this->datas["`" . static::STATE . "`"])) {
                $this->data(static::STATE, static::_STATE_ENABLED, false);
            }
            if ($this->create($datas)) {
                Log::addMe($id_type_log ?: Log::CREATE,
                    $this->getID(), $this->table, $log_data, $log_id_parent, $log_parent);
                return Rsp::okSaved()
                    ->set('id', $this->getID())
                    ->set('is_new', true);
            } else {
                return $this->last_error ? rsp('Error DB: ' . $this->last_error) : Rsp::e500();
            }
        }
    }

    public static function deleteRSP($id, $forever = false)
    {
        return static::find($id)->removeRSP($forever);
    }

    public function removeRSP($forever = false)
    {
        if ($this->exist()) {
            if ($this->delete($forever)) {
                Log::add(Log::DELETE, Auth::id(), $this->getID(), $this->table);
                return Rsp::okRemoved();
            } else return Rsp::e500();
        } else return Rsp::e404();
    }

    /**
     * @param bool $forever
     * @return bool
     */
    public function delete($forever = false)
    {
        if ($this->exist()) {
            if ($forever) {
                return $this->QB()
                    ->where(static::ID, '=', $this->getID())
                    ->delete();
            } else {
                $this->data(static::STATE, static::_STATE_DELETED, false);
                if (!is_null(static::DATE_DELETED)) {
                    $this->data(static::DATE_DELETED, Raw::now(), false);
                }
                return $this->update();
            }
        }
        return false;
    }

    public static function enableRSP($id)
    {
        $item = static::find($id);
        if ($item->exist()) {
            return $item->saveRSP([
                static::STATE => static::_STATE_ENABLED,
            ], Log::ENABLE);
        } else return Rsp::e404();
    }

    public static function disableRSP($id)
    {
        $item = static::find($id);
        if ($item->exist()) {
            return $item->saveRSP([
                static::STATE => static::_STATE_DISABLED,
            ], Log::DISABLE);
        } else return Rsp::e404();
    }

    public function getID()
    {
        $attr_id = static::ID;
        return isset($this->$attr_id) ? $this->$attr_id : '';
    }

    /**
     * @return bool
     */
    public function exist()
    {
        return !empty($this->getID());
    }

    /* @return QB */
    public function QB()
    {
        return QB::table($this->table);
    }

    static function id($id)
    {
        $new = new static();
        $attr_id = static::ID;
        $new->$attr_id = $id;
        return $new;
    }

    # ESTADOS genericos
    static function states()
    {
        return [
            BaseModel::_STATE_ENABLED  => new State('#0abb87', 'Activo'),
            BaseModel::_STATE_DISABLED => new State('#ffb822', 'Inactivo'),
            BaseModel::_STATE_DELETED  => new State('#fd397a', 'Eliminado'),
        ];
    }

    # ESTADOS genericos
    static function statesArr($states = null)
    {
        if (is_null($states))
            $states = static::states();
        $items = [];
        foreach ($states as $id => $state) {
            $state->id = $id;
            $items[] = $state;
        }
        return $items;
    }

    public function stateO()
    {
        return self::stateObj($this->{static::STATE});
    }

    public static function stateObj($state)
    {
        $map = static::states();
        return isset($map[$state]) ? $map[$state] : new State();
    }

    # obtener el siguiente id
    public static function nextID()
    {
        $ins = new static;
        $last = $ins->QB()
            ->orderBy(static::ID, 'DESC')
            ->first();
        return $last ? $last->{static::ID} + 1 : 1;
    }

    public function logMe($id_type_log, $data = '', $id_parent = 0, $parent = '')
    {
        Log::addMe($id_type_log, $this->getID(), $this->table, $data, $id_parent, $parent);
    }

}
