<?php


namespace Copper\Component\DB;



/**
 * Class DBColumnModAction
 * @package Copper\Component\DB
 */
class DBColumnModAction
{
    const SUB = 1;
    const ADD = 2;
    const DIV = 3;
    const MUL = 4;
    const SUB_PERC = 5;
    const ADD_PERC = 6;

    /**@var int */
    private $type;
    /** @var string|int|float|null */
    private $value;

    /**
     * DBColumnMod constructor.
     * @param int $type
     * @param string|int|float $value
     */
    public function __construct(int $type, $value)
    {
        $this->type = $type;
        $this->value = $this->formatValue($value);
    }

    /**
     * @param int $type
     * @param string|int|float $value
     *
     * @return float|string
     */
    private function formatValue($value)
    {
        if (is_string($value))
            $value = '`' . DBModel::formatFieldName($value) . '`';

        return $value;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return float|int|string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}