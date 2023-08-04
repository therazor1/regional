<?php namespace Inc\validator_rules;

use Libs\Pixie\QB;
use Rakit\Validation\Rule;

class ExistRule extends Rule
{
    protected $message = ":attribute :value no existe";

    protected $fillableParams = ['table', 'column'];

    public function check($value): bool
    {
        $this->requireParameters(['table', 'column']);

        $table = $this->parameter('table');
        $column = $this->parameter('column');

        return QB::query("
            SELECT * FROM $table WHERE $column = '$value'
        ")->first() != null;
    }
}