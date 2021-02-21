<?php


namespace Copper\Component\DB;


class DBWhereEntry
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
        return DBModel::formatFieldName($this->field);
    }

    public function formatValue()
    {
        $value = $this->value;

        if (is_bool($value) === true)
            $value = intval($value);

        if (in_array($this->cond, [
            DBWhere::BETWEEN,
            DBWhere::BETWEEN_INCLUDE,
            DBWhere::NOT_BETWEEN,
            DBWhere::NOT_BETWEEN_INCLUDE
        ])) {
            $value[0] = DBModel::formatNumber($value[0]);
            $value[1] = DBModel::formatNumber($value[1]);
        }

        return $value;
    }
}