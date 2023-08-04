<?php namespace Libs\Pixie;

use Closure;
use DateTime;
use DateTimeZone;
use Exception;
use PDO;
use PDOStatement;

class QB
{
    /* @var array */
    protected $statements = array();

    /* @var PDO */
    protected $pdo;

    /* @var null|PDOStatement */
    protected $pdoStatement = null;

    /* @var Mysql */
    protected $adapter;

    /**
     * The PDO fetch parameters to use
     * @var array
     */
    protected $fetchParameters = array(PDO::FETCH_OBJ);

    public function __construct()
    {
        $this->pdo = self::cn();

        // Query builder adapter instance
        $this->adapter = new Mysql();

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        #$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    }

    /** @var PDO */
    protected static $cn;

    public static function cn($db_host = null, $db_name = null, $db_user = null, $db_pass = null)
    {
        $db_host = $db_host ?: DB_HOST;
        $db_name = $db_name ?: DB_NAME;
        $db_user = $db_user ?: DB_USER;
        $db_pass = $db_pass ?: DB_PASS;

        if (!static::$cn) {
            try {
                static::$cn = new PDO("mysql:dbname=" . $db_name . ";host=" . $db_host, $db_user, $db_pass);
                static::$cn->prepare("SET NAMES 'utf8';")->execute();
                #static::$cn->prepare("SET sql_mode = 'NO_ZERO_DATE';")->execute();
            } catch (Exception $e) {
                exit('Error al intentar conectar con la base de datos (PDO) <h1>' . $e->getMessage() . '</h1>');
            }
        }
        return static::$cn;
    }

    public static function setupBase($timezone)
    {
        date_default_timezone_set($timezone);

        $tz = (new DateTime('now', new DateTimeZone($timezone)))->format('P');
        self::cn()->exec("
            SET NAMES 'utf8';
            SET GLOBAL time_zone = '$tz';
        ");
    }

    public static function date()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Set the fetch mode
     *
     * @param $mode
     * @return $this
     */
    public function setFetchMode($mode)
    {
        $this->fetchParameters = func_get_args();
        return $this;
    }

    /**
     * Fetch query results as object of specified type
     *
     * @param $className
     * @param array $constructorArgs
     * @return self
     */
    public function asObject($className, $constructorArgs = array())
    {
        return $this->setFetchMode(PDO::FETCH_CLASS, $className, $constructorArgs);
    }

    /**
     * @return self
     */
    public function newQuery()
    {
        return new static();
    }

    /**
     * @param       $sql
     * @param array $bindings
     *
     * @return self
     */
    public static function query($sql, $bindings = null)
    {
        if (!is_array($bindings)) {
            $bindings = func_get_args();
            array_splice($bindings, 0, 1);
        }

        $instance = new static();

        $instance->pdoStatement = $instance->statement($sql, $bindings);

        return $instance;
    }

    public string $last_error = '';

    /**
     * @param       $sql
     * @param array $bindings
     *
     * @return PDOStatement|bool
     */
    public function statement($sql, $bindings = array())
    {
        $pdoStatement = $this->pdo->prepare($sql);
        foreach ($bindings as $key => $value) {
            $pdoStatement->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value,
                is_int($value) || is_bool($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        try {
            $pdoStatement->execute();
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            return false;
        }
        return $pdoStatement;
    }

    /**
     * Get all rows
     *
     * @return mixed
     */

    public function get()
    {
        if (is_null($this->pdoStatement)) {
            $queryObject = $this->getQuery('select');
            $this->pdoStatement = $this->statement(
                $queryObject->getSql(),
                $queryObject->getBindings()
            );
        }

        $result = call_user_func_array(array($this->pdoStatement, 'fetchAll'), $this->fetchParameters);

        $this->pdoStatement = null;

        return $result;
    }

    /**
     * Get first row
     *
     * @return mixed
     */
    public function first()
    {
        $this->limit(1);
        $result = $this->get();
        return empty($result) ? null : $result[0];
    }

    /**
     * @param        $value
     * @param string $fieldName
     *
     * @return null|\stdClass
     */
    public function findAll($fieldName, $value)
    {
        $this->where($fieldName, '=', $value);
        return $this->get();
    }

    /**
     * @param        $value
     * @param string $fieldName
     *
     * @return mixed
     */
    public function find($value, $fieldName = 'id')
    {
        $this->where($fieldName, '=', $value);
        return $this->first();
    }

    /**
     * Get count of rows
     *
     * @return int
     */
    public function count()
    {
        // Get the current statements
        $originalStatements = $this->statements;

        unset($this->statements['orderBys']);
        unset($this->statements['limit']);
        unset($this->statements['offset']);


        # problemas cuando hay group by, detectarlo
        if (isset($this->statements['groupBys'])) {
            $count = (int)self::query("
                SELECT COUNT(*) total
                FROM (" . $this->getSQL() . ") as tst;
            ")->get()[0]->total;
        } else {
            $count = $this->aggregate('count');
        }

        $this->statements = $originalStatements;

        return $count;
    }

    /**
     * @param $type
     *
     * @return int
     */
    protected function aggregate($type)
    {
        // Get the current selects
        $mainSelects = isset($this->statements['selects']) ? $this->statements['selects'] : null;
        // Replace select with a scalar value like `count`
        $this->statements['selects'] = array($this->raw($type . '(*) as field'));
        $row = $this->get();

        // Set the select as it was
        if ($mainSelects) {
            $this->statements['selects'] = $mainSelects;
        } else {
            unset($this->statements['selects']);
        }

        if (is_array(@$row[0])) {
            return (int)$row[0]['field'];
        } elseif (is_object(@$row[0])) {
            return (int)$row[0]->field;
        }

        return 0;
    }

    /**
     * @param string $type
     * @param array $dataToBePassed
     *
     * @return QueryObject
     * @throws Exception
     */
    public function getQuery($type = 'select', $dataToBePassed = array())
    {
        $allowedTypes = array('select', 'insert', 'insertignore', 'replace', 'delete', 'update', 'criteriaonly');
        if (!in_array(strtolower($type), $allowedTypes)) {
            throw new Exception($type . ' is not a known type.', 2);
        }

        $queryArr = $this->adapter->$type($this->statements, $dataToBePassed);

        return new QueryObject($queryArr['sql'], $queryArr['bindings'], $this->pdo);
    }

    /**
     * @param self $queryBuilder
     * @param null $alias
     *
     * @return Raw
     */
    public function subQuery($queryBuilder, $alias = null)
    {
        $sql = '(' . $queryBuilder->getQuery()->getRawSql() . ')';
        if ($alias) {
            $sql = $sql . ' as ' . $alias;
        }

        return $queryBuilder->raw($sql);
    }

    /**
     * @param $data
     * @param $type
     * @return array|bool|int
     */
    private function doInsert($data, $type)
    {
        // Si el primer valor no es una matriz, no es una inserción por lotes
        if (!is_array(current($data))) {
            $queryObject = $this->getQuery($type, $data);

            $result = $this->statement($queryObject->getSql(), $queryObject->getBindings());

            $return = ($result && $result->rowCount() === 1) ? $this->pdo->lastInsertId() : false;

        } else {
            // Es un inserto por lotes
            $return = array();

            foreach ($data as $subData) {
                $queryObject = $this->getQuery($type, $subData);

                $result = $this->statement($queryObject->getSql(), $queryObject->getBindings());

                if ($result->rowCount() === 1) {
                    $return[] = (int)$this->pdo->lastInsertId();
                }
            }
        }

        return $return;
    }

    /**
     * @param $data
     *
     * @return array|string
     */
    public function insert($data)
    {
        return $this->doInsert($data, 'insert');
    }

    /**
     * @param $data
     *
     * @return array|string
     */
    public function insertIgnore($data)
    {
        return $this->doInsert($data, 'insertignore');
    }

    /**
     * @param $data
     *
     * @return array|string
     */
    public function replace($data)
    {
        return $this->doInsert($data, 'replace');
    }

    /**
     * @param $data
     * @return bool
     */
    public function update($data)
    {
        $queryObject = $this->getQuery('update', $data);
        return $this->statement($queryObject->getSql(), $queryObject->getBindings()) != false;
    }

    /**
     * @param $data
     *
     * @return array|string
     */
    public function updateOrInsert($data)
    {
        if ($this->first()) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * @param $data
     *
     * @return $this
     */
    public function onDuplicateKeyUpdate($data)
    {
        $this->addStatement('onduplicate', $data);
        return $this;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $queryObject = $this->getQuery('delete');

        $response = $this->statement($queryObject->getSql(), $queryObject->getBindings());

        return $response != false;
    }

    /**
     * @param string $tables Single table or multiple tables as an array or as
     *                multiple parameters
     *
     * @return self
     */
    public static function table($tables)
    {
        if (!is_array($tables)) {
            // because a single table is converted to an array anyways,
            // this makes sense.
            $tables = func_get_args();
        }

        $instance = new static();

        $instance->addStatement('tables', $tables);
        return $instance;
    }

    /**
     * @param $tables
     *
     * @return $this
     */
    public function from($tables)
    {
        if (!is_array($tables)) {
            $tables = func_get_args();
        }

        $this->addStatement('tables', $tables);
        return $this;
    }

    /**
     * @param $fields
     *
     * @return $this
     */
    public function select($fields)
    {
        if (!is_array($fields)) {
            $fields = func_get_args();
        }

        $this->addStatement('selects', $fields);
        return $this;
    }

    /**
     * @param $fields
     *
     * @return $this
     */
    public function selectDistinct($fields)
    {
        $this->select($fields);
        $this->addStatement('distinct', true);
        return $this;
    }

    /**
     * @param $field
     *
     * @return $this
     */
    public function groupBy($field)
    {
        $this->addStatement('groupBys', $field);
        return $this;
    }

    /**
     * @param        $fields
     * @param string $defaultDirection
     *
     * @return $this
     */
    public function orderBy($fields, $defaultDirection = 'ASC')
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        foreach ($fields as $key => $value) {
            $field = $key;
            $type = $value;
            if (is_int($key)) {
                $field = $value;
                $type = $defaultDirection;
            }
            $this->statements['orderBys'][] = compact('field', 'type');
        }

        return $this;
    }

    /**
     * @param $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->statements['limit'] = $limit;
        return $this;
    }

    /**
     * @param $offset
     *
     * @return $this
     */
    public function offset($offset)
    {
        $this->statements['offset'] = $offset;
        return $this;
    }

    /**
     * @param        $key
     * @param        $operator
     * @param        $value
     * @param string $joiner
     *
     * @return $this
     */
    public function having($key, $operator, $value, $joiner = 'AND')
    {
        $this->statements['havings'][] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    /**
     * @param        $key
     * @param        $operator
     * @param        $value
     *
     * @return $this
     */
    public function orHaving($key, $operator, $value)
    {
        return $this->having($key, $operator, $value, 'OR');
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     *
     * @return $this
     */
    public function where($key, $operator = null, $value = null)
    {
        // Si el primer parametro es numerico, se entiende que es id = $key
        if (is_numeric($key) && $key > 0) {
            $value = $key;
            $key = 'id';
            $operator = '=';
        } // If two params are given then assume operator is =
        else if (is_null($value)) {
            $value = $operator;
            $operator = '=';
        }
        return $this->whereHandler($key, $operator, $value);
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     *
     * @return $this
     */
    public function orWhere($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->whereHandler($key, $operator, $value, 'OR');
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     *
     * @return $this
     */
    public function whereNot($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->whereHandler($key, $operator, $value, 'AND NOT');
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     *
     * @return $this
     */
    public function orWhereNot($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->whereHandler($key, $operator, $value, 'OR NOT');
    }

    /**
     * @param       $key
     * @param array $values
     *
     * @return $this
     */
    public function whereIn($key, $values)
    {
        return $this->whereHandler($key, 'IN', $values, 'AND');
    }

    /**
     * @param       $key
     * @param array $values
     *
     * @return $this
     */
    public function whereNotIn($key, $values)
    {
        return $this->whereHandler($key, 'NOT IN', $values, 'AND');
    }

    /**
     * @param       $key
     * @param array $values
     *
     * @return $this
     */
    public function orWhereIn($key, $values)
    {
        return $this->whereHandler($key, 'IN', $values, 'OR');
    }

    /**
     * @param       $key
     * @param array $values
     *
     * @return $this
     */
    public function orWhereNotIn($key, $values)
    {
        return $this->whereHandler($key, 'NOT IN', $values, 'OR');
    }

    /**
     * @param $key
     * @param $valueFrom
     * @param $valueTo
     *
     * @return $this
     */
    public function whereBetween($key, $valueFrom, $valueTo)
    {
        return $this->whereHandler($key, 'BETWEEN', array($valueFrom, $valueTo), 'AND');
    }

    /**
     * @param $key
     * @param $valueFrom
     * @param $valueTo
     *
     * @return $this
     */
    public function orWhereBetween($key, $valueFrom, $valueTo)
    {
        return $this->whereHandler($key, 'BETWEEN', array($valueFrom, $valueTo), 'OR');
    }

    /**
     * @param $key
     * @return self
     */
    public function whereNull($key)
    {
        return $this->whereNullHandler($key);
    }

    /**
     * @param $key
     * @return self
     */
    public function whereNotNull($key)
    {
        return $this->whereNullHandler($key, 'NOT');
    }

    /**
     * @param $key
     * @return self
     */
    public function orWhereNull($key)
    {
        return $this->whereNullHandler($key, '', 'or');
    }

    /**
     * @param $key
     * @return self
     */
    public function orWhereNotNull($key)
    {
        return $this->whereNullHandler($key, 'NOT', 'or');
    }

    protected function whereNullHandler($key, $prefix = '', $operator = '')
    {
        $key = $this->adapter->wrapSanitizer($key);
        return $this->{$operator . 'Where'}($this->raw("{$key} IS {$prefix} NULL"));
    }

    /**
     * @param        $table
     * @param        $key
     * @param        $operator
     * @param        $value
     * @param string $type
     *
     * @return $this
     */
    public function join($table, $key, $operator = null, $value = null, $type = 'inner')
    {
        if (!$key instanceof Closure) {
            $key = function ($joinBuilder) use ($key, $operator, $value) {
                $joinBuilder->on($key, $operator, $value);
            };
        }

        // Build a new JoinBuilder class, keep it by reference so any changes made
        // in the closure should reflect here
        $joinBuilder = new JoinBuilder();
        // Call the closure with our new joinBuilder object
        $key($joinBuilder);
        // Get the criteria only query from the joinBuilder object
        $this->statements['joins'][] = compact('type', 'table', 'joinBuilder');

        return $this;
    }

    /**
     * @param      $table
     * @param      $key
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function leftJoin($table, $key, $operator = null, $value = null)
    {
        return $this->join($table, $key, $operator, $value, 'left');
    }

    /**
     * @param      $table
     * @param      $key
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function rightJoin($table, $key, $operator = null, $value = null)
    {
        return $this->join($table, $key, $operator, $value, 'right');
    }

    /**
     * @param      $table
     * @param      $key
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function innerJoin($table, $key, $operator = null, $value = null)
    {
        return $this->join($table, $key, $operator, $value, 'inner');
    }

    /**
     * Add a raw query
     *
     * @param $value
     * @param $bindings
     *
     * @return mixed
     */
    public static function raw($value, $bindings = array())
    {
        return new Raw($value, $bindings);
    }

    /**
     * @param        $key
     * @param        $operator
     * @param        $value
     * @param string $joiner
     *
     * @return $this
     */
    protected function whereHandler($key, $operator = null, $value = null, $joiner = 'AND')
    {
        $this->statements['wheres'][] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function addStatement($key, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        if (!array_key_exists($key, $this->statements)) {
            $this->statements[$key] = $value;
        } else {
            $this->statements[$key] = array_merge($this->statements[$key], $value);
        }
    }

    /**
     * @return array
     */
    public function getStatements()
    {
        return $this->statements;
    }

    public function getSQL()
    {
        return $this->getQuery()->getRawSql();
    }

    public function whereLike($key, $value)
    {
        return $this->where($key, 'LIKE', '%' . str_replace(' ', '%', $value) . '%');
    }

    //TODO: debug sql raw
    public function showSQL($exit = true)
    {
        $sql = '<hr>' . \Libs\SqlFormatter::format($this->getSQL());

        if ($exit)
            exit($sql);
        else
            echo $sql;
    }


}
