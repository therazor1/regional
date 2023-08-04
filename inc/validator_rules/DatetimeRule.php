<?php namespace Inc\validator_rules;

use Inc\Util;
use Rakit\Validation\Rule;
use Rakit\Validation\Rules\Interfaces\ModifyValue;

class DatetimeRule extends Rule implements ModifyValue
{
    protected $message = ':attribute fecha/hora no válida';

    public function check($value): bool
    {
        return Util::isDateTime($value);
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
        return str_replace('T', ' ', $value);
    }
}