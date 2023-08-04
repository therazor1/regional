<?php namespace Inc\validator_rules;

use Rakit\Validation\Rule;
use Rakit\Validation\Rules\Interfaces\ModifyValue;

class BoolRule extends Rule implements ModifyValue
{
    protected $message = 'El campo :attribute debe ser un número mayor a 0';

    public function check($value): bool
    {
        return true;
    }

    /**
     * Modify given value
     * so in current and next rules returned value will be used
     *
     * @param mixed $value
     * @return mixed
     */
    public function modifyValue($value)
    {
        return ($value == '1' || $value == 'true' || $value == 'on') ? 1 : 0;
    }
}