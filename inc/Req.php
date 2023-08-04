<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Inc;

use Inc\validator_rules\BoolRule;
use Inc\validator_rules\DatetimeRule;
use Inc\validator_rules\ExistRule;
use Inc\validator_rules\IdRule;
use Inc\validator_rules\NumRule;
use Inc\validator_rules\UniqueRule;
use Libs\Pixie\QB;
use Rakit\Validation\Validation;
use Rakit\Validation\Validator;
use stdClass;

class Req extends stdClass
{
    private $items = [];

    public function __construct($items = null)
    {
        $this->setAll($items);
    }

    function get($name, $default_value = '')
    {
        return isset($this->items[$name]) ? $this->items[$name] : $default_value;
    }

    function any($name, $default_value = '')
    {
        $val = @trim(@$this->items[$name]);
        return !empty($val) ? $val : $default_value;
    }

    function num($name, $default_value = 0)
    {
        $val = @$this->items[$name];
        return is_numeric($val) ? $val + 0 : $default_value;
    }

    function bool($name, $default_value = 0)
    {
        $val = @$this->items[$name];
        return ($val == '1' || $val == 'true' || $val == 'on') ? 1 : $default_value;
    }

    function date($name, $default_value = '')
    {
        $val = @$this->items[$name];
        return !empty($val) && Util::isDate($val) ? $val : $default_value;
    }

    function datetime($name, $default_value = '')
    {
        $val = @$this->items[$name];
        $val = str_replace('T', ' ', $val);
        return !empty($val) && Util::isDateTime($val) ? $val : $default_value;
    }

    function color($name, $default_value = '')
    {
        $val = @trim(@$this->items[$name]);
        return (substr($val, 0, 1) === '#' && strlen($val) === 7) ? $val : $default_value;
    }

    function arr($name, $default_value = [])
    {
        $val = @$this->items[$name];
        return is_array($val) ? $val : $default_value;
    }

    function arrComma($name, $default_value = [])
    {
        $val = @$this->items[$name];
        return explod($val) ?: $default_value;
    }

    function arrJSON($name, $default_value = [])
    {
        return json_decode(@$this->items[$name]) ?: $default_value;
    }

    function json($name)
    {
        return json_decode(@$this->items[$name]) ?: new stdClass();
    }

    function set($key, $val)
    {
        $this->items[$key] = $val;
    }

    function setAll($items)
    {
        if ($items != null) {
            foreach ($items as $k => $v) {
                $this->set($k, $v);
            }
        }
    }

    function getAll()
    {
        return $this->items;
    }

    /**
     * Ayuda de id
     * @return int
     */
    function id()
    {
        return $this->num('id');
    }

    /**
     * Ayuda de id requerido
     * @return int
     */
    function requiredId($field = 'id')
    {
        $data = $this->data([$field => 'required|id']);
        return $data->{$field};
    }

    /**
     * Obtener un valor validando
     * @param $name
     * @param $rule
     * @return mixed
     */
    function valid($name, $rule)
    {
        return $this->data([$name => $rule])->{$name};
    }

    /**
     * @param array $values
     * @return Validation
     */
    function validate($values)
    {
        $validation = $this->_validate($values);

        $errors = [];

        if ($validation->fails()) {
            $messages = $validation->errors()->toArray();

            foreach ($messages as $key => $keyMessages) {
                $errors[$key] = array_shift($messages[$key]);
            }

        }

        if ($errors) {
            $first_value = reset($errors);
            rsp($first_value)->set('errors', $errors)->done();
        }

        return $validation;
    }

    /**
     * @param array $values
     * @return string | null retorna null si no hay errores
     */
    function validateFirstError($values)
    {
        $validation = $this->_validate($values);

        $errors = [];

        if ($validation->fails()) {
            $messages = $validation->errors()->toArray();

            foreach ($messages as $key => $keyMessages) {
                $errors[$key] = array_shift($messages[$key]);
            }

        }

        if ($errors) {
            return reset($errors);
        }

        return null; # sin errores
    }

    /**
     * @param array $values
     * @return stdClass
     */
    function data(array $values)
    {
        $validation = $this->validate($values);
        return (object)$validation->getValidData();
    }

    function _validate($values)
    {
        $rules = [];

        $aliases = [];

        foreach ($values as $k => $v) {
            if (is_array($v)) {
                $key = key($v);
                $rules[$k] = $key;
                $aliases[$k] = $v[$key];
            } else {
                $rules[$k] = $v;
            }
        }

        $validator = new Validator([
            'required'      => 'El campo :attribute es requerido',
            'required_if'   => 'El campo :attribute es requerido',
            'email'         => 'El campo :attribute no es correo electrónico válido',
            'url'           => 'El campo :attribute no es una URL válida',
            'alpha_num'     => 'El campo :attribute solo permite letras alfabéticas y numéricas',
            'min'           => 'El mínimo de :attribute es :min',
            'max'           => 'El máximo de :attribute es :max',
            'in'            => 'El campo :attribute solo permite :allowed_values',
            'uploaded_file' => 'El archivo :attribute es demasiado grande, el tamaño máximo es :max_size',
        ]);
        $validator->addValidator('unique', new UniqueRule());
        $validator->addValidator('exist', new ExistRule());
        $validator->addValidator('datetime', new DatetimeRule());
        $validator->addValidator('num', new NumRule());
        $validator->addValidator('bool', new BoolRule());
        $validator->addValidator('id', new IdRule());
        $validation = $validator->make($this->items + $_FILES, $rules);

        if ($aliases)
            $validation->setAliases($aliases);

        $validation->validate();

        return $validation;
    }

    /**
     * desde base64 json
     * @param $name
     * @return Req
     */
    function decodeTK($name = 'tk')
    {
        $value = $this->any($name);
        $b64_decoded = base64_decode($value);
        $json_decoded = json_decode($b64_decoded) ?: [];
        return new Req($json_decoded);
    }

    /**
     * @param array $params
     * @return bool|string
     */
    public static function encodeTK($params)
    {
        $params['_created'] = QB::date();
        $params['_expires'] = QB::date();
        return base64_encode(json_encode($params));
    }

    /**
     * Obtener filtros
     * @param $merge
     * @return Fil
     */
    public function fil($merge = null)
    {
        $fil = new Fil();
        $fil->page = $this->num('page');
        $fil->limit = $this->num('limit', 10);
        $fil->order_col = $this->any('order_col', 'id');
        $fil->order_dir = $this->any('order_dir', 'DESC');
        $fil->id = $this->any('id');
        $fil->query = $this->any('query');
        $fil->date_from = $this->date('date_from');
        $fil->date_to = $this->date('date_to');
        $fil->state = $this->num('state', -1);
        $fil->export = $this->any('export');
        $fil->value = $this->num('value'); # para el valor del autocomplete
        if ($merge) {
            $fil->merge($merge);
        }
        return $fil;
    }
}