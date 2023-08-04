<?php namespace Inc\validator_rules;

use Libs\Pixie\QB;
use Rakit\Validation\Rule;

class UniqueRule extends Rule
{
    protected $message = ":attribute :value ha sido utilizado";

    protected $fillableParams = ['table', 'column', 'except'];

    public function check($value): bool
    {
        $this->requireParameters(['table', 'column']);

        $table = $this->parameter('table');
        $column = $this->parameter('column');
        $except = $this->parameter('except');

        if ($except AND $except == $value) {
            return true;
        }

        return QB::query("
            SELECT * FROM $table WHERE $column = '$value' AND id != '$except'
        ")->first() === null;
    }
}