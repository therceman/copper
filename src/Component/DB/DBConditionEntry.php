<?php


namespace Copper\Component\DB;


class DBConditionEntry
{
    /** @var string */
    public $field;
    /** @var string|int|float|null */
    public $value;
    /** @var int */
    public $cond;
    /** @var int */
    public $chain;

    public function __construct(string $field, $value, int $cond, int $chain)
    {
        $this->field = $field;
        $this->value = $value;
        $this->cond = $cond;
        $this->chain = $chain;
    }

    public function formatField()
    {
        $field = $this->field;

        $field = preg_replace("/[^a-zA-Z0-9_]+/", "", $field);

        return $field;
    }

    public function formatValue()
    {
        $value = $this->value;

        if (is_bool($value) === true)
            $value = intval($value);

        return $value;
    }
}