<?php namespace Inc\validator_rules;

use Libs\Pixie\QB;
use Rakit\Validation\Rule;
use Rakit\Validation\Rules\Interfaces\ModifyValue;

class IdRule extends Rule implements ModifyValue
{
    protected $message = 'El campo :attribute no es válido';

    protected $fillableParams = ['table'];

    public function check($value): bool
    {
        $table = $this->parameter('table');

        $attribute = $this->getAttribute();
        $isRequired = $attribute->isRequired();

        if ($value > 0) {
            if ($table) {
                if (QB::table($table)->where('id', $value)->first()) {
                    return true;
                } else {
                    $this->setMessage(':attribute :value no existe');
                    return false;
                }
            } else {
                return true;
            }
        } else if ($isRequired) {
            return false;
        } else {
            return true;
        }
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
        return is_numeric($value) ? $value + 0 : 0;
    }
}