<?php


namespace Copper\Component\DB;


use Envms\FluentPDO\Queries\Delete;
use Envms\FluentPDO\Queries\Select;
use Envms\FluentPDO\Queries\Update;

class DBCondition
{
    const IS = 1;
    const NOT = 2;
    const LT = 3;
    const GT = 4;
    const LT_OR_EQ = 5;
    const GT_OR_EQ = 6;
    const BETWEEN = 7;
    const BETWEEN_INCLUDE = 8;
    const NOT_BETWEEN = 9;
    const NOT_BETWEEN_INCLUDE = 10;
    const LIKE = 11;
    const NOT_LIKE = 12;
    // TODO IN & NOT IN

    const CHAIN_NULL = 20;
    const CHAIN_OR = 21;
    const CHAIN_AND = 22;

    /** @var DBConditionEntry[] */
    private $conditions;

    /**
     * DBCondition constructor.
     *
     * @param string $field
     * @param string|int|float|array|null $value
     * @param int $cond
     * @param int $chain
     */
    public function __construct(string $field, $value, int $cond, int $chain)
    {
        $this->conditions = [];

        $this->addCondition($field, $value, $cond, $chain);
    }

    /**
     * Add Condition
     * @param string $field
     * @param string|int|float|null|array $value
     * @param int $cond
     * @param int $chain
     */
    private function addCondition(string $field, $value, int $cond, int $chain)
    {
        $this->conditions[] = new DBConditionEntry($field, $value, $cond, $chain);
    }

    /**
     * @return DBConditionEntry
     */
    private function lastCondition()
    {
        return end($this->conditions);
    }

    /**
     * Less Than
     *
     * @param string $field
     * @param int|float $value
     *
     * @return DBCondition
     */
    public static function lt(string $field, $value)
    {
        return new self($field, $value, self::LT, self::CHAIN_NULL);
    }

    /**
     * Less Than OR Equal
     *
     * @param string $field
     * @param int|float $value
     *
     * @return DBCondition
     */
    public static function ltOrEq(string $field, $value)
    {
        return new self($field, $value, self::LT_OR_EQ, self::CHAIN_NULL);
    }

    /**
     * Greater Than
     *
     * @param string $field
     * @param int|float $value
     *
     * @return DBCondition
     */
    public static function gt(string $field, $value)
    {
        return new self($field, $value, self::GT, self::CHAIN_NULL);
    }

    /**
     * Greater Than OR Equal
     *
     * @param string $field
     * @param int|float $value
     *
     * @return DBCondition
     */
    public static function gtOrEq(string $field, $value)
    {
        return new self($field, $value, self::GT_OR_EQ, self::CHAIN_NULL);
    }

    /**
     * Is
     *
     * @param string $field
     * @param mixed $value
     *
     * @return DBCondition
     */
    public static function is(string $field, $value)
    {
        return new self($field, $value, self::IS, self::CHAIN_NULL);
    }

    /**
     * Not
     *
     * @param string $field
     * @param mixed $value
     *
     * @return DBCondition
     */
    public static function not(string $field, $value)
    {
        return new self($field, $value, self::NOT, self::CHAIN_NULL);
    }

    /**
     * Between
     * E.g. buyPrice > 20 OR buyPrice < 100
     *
     * @param string $field
     * @param int|float $start
     * @param int|float $end
     *
     * @return DBCondition
     */
    public static function between(string $field, $start, $end)
    {
        return new self($field, [$start, $end], self::BETWEEN, self::CHAIN_NULL);
    }

    /**
     * Between Include
     * E.g. buyPrice >= 20 OR buyPrice <= 100
     *
     * @param string $field
     * @param int|float $start
     * @param int|float $end
     *
     * @return DBCondition
     */
    public static function betweenInclude(string $field, $start, $end)
    {
        return new self($field, [$start, $end], self::BETWEEN_INCLUDE, self::CHAIN_NULL);
    }

    /**
     * Not Between
     * E.g. buyPrice < 20 OR buyPrice > 100
     *
     * @param string $field
     * @param int|float $start
     * @param int|float $end
     *
     * @return DBCondition
     */
    public static function notBetween(string $field, $start, $end)
    {
        return new self($field, [$start, $end], self::NOT_BETWEEN, self::CHAIN_NULL);
    }

    /**
     * NOT Between Include
     * E.g. buyPrice <= 20 OR buyPrice >= 100
     *
     * @param string $field
     * @param int|float $start
     * @param int|float $end
     *
     * @return DBCondition
     */
    public static function notBetweenInclude(string $field, $start, $end)
    {
        return new self($field, [$start, $end], self::NOT_BETWEEN_INCLUDE, self::CHAIN_NULL);
    }

    /**
     * Like
     *
     * a%   - Finds any values that start with "a";
     *
     * %a   - Finds any values that end with "a"
     *
     * %or% - Finds any values that have "or" in any position
     *
     * _r%  - Finds any values that have "r" in the second position
     *
     * a_%  - Finds any values that start with "a" and are at least 2 characters in length
     *
     * a__% - Finds any values that start with "a" and are at least 3 characters in length
     *
     * a%o  - Finds any values that start with "a" and ends with "o"
     *
     * @param string $field
     * @param mixed $value
     *
     * @return DBCondition
     */
    public static function like(string $field, $value)
    {
        return new self($field, $value, self::LIKE, self::CHAIN_NULL);
    }

    /**
     * Not Like
     *
     * (see like documentation for all cases)
     *
     * @param string $field
     * @param mixed $value
     *
     * @return DBCondition
     */
    public static function notLike(string $field, $value)
    {
        return new self($field, $value, self::NOT_LIKE, self::CHAIN_NULL);
    }

    // ------------ Shortcuts ---------------

    public static function notNull($field)
    {
        return self::not($field, null);
    }

    /**
     * Is Like
     *
     * (see like for documentation)
     *
     * @param string $field
     * @param mixed $value
     *
     * @return DBCondition
     */
    public static function isLike(string $field, $value)
    {
        return self::like($field, $value);
    }

    // ------------ Chain ---------------

    public function and($field, $value)
    {
        $this->addCondition($field, $value, self::IS, self::CHAIN_AND);

        return $this;
    }

    public function or($field, $value)
    {
        $this->addCondition($field, $value, self::IS, self::CHAIN_OR);

        return $this;
    }

    public function andNot($field, $value)
    {
        $this->addCondition($field, $value, self::NOT, self::CHAIN_AND);

        return $this;
    }

    public function orNot($field, $value)
    {
        $this->addCondition($field, $value, self::NOT, self::CHAIN_OR);

        return $this;
    }

    public function andBetween($field, $start, $end)
    {
        $this->addCondition($field, [$start, $end], self::BETWEEN, self::CHAIN_AND);

        return $this;
    }

    public function andBetweenInclude($field, $start, $end)
    {
        $this->addCondition($field, [$start, $end], self::BETWEEN_INCLUDE, self::CHAIN_AND);

        return $this;
    }

    public function orBetween($field, $start, $end)
    {
        $this->addCondition($field, [$start, $end], self::BETWEEN, self::CHAIN_OR);

        return $this;
    }

    public function orBetweenInclude($field, $start, $end)
    {
        $this->addCondition($field, [$start, $end], self::BETWEEN_INCLUDE, self::CHAIN_OR);

        return $this;
    }

    public function andNotBetween($field, $start, $end)
    {
        $this->addCondition($field, [$start, $end], self::NOT_BETWEEN, self::CHAIN_AND);

        return $this;
    }

    public function andNotBetweenInclude($field, $start, $end)
    {
        $this->addCondition($field, [$start, $end], self::NOT_BETWEEN_INCLUDE, self::CHAIN_AND);

        return $this;
    }

    public function orNotBetween($field, $start, $end)
    {
        $this->addCondition($field, [$start, $end], self::NOT_BETWEEN, self::CHAIN_OR);

        return $this;
    }

    public function orNotBetweenInclude($field, $start, $end)
    {
        $this->addCondition($field, [$start, $end], self::NOT_BETWEEN_INCLUDE, self::CHAIN_OR);

        return $this;
    }

    public function orLt($field, $value)
    {
        $this->addCondition($field, $value, self::LT, self::CHAIN_OR);

        return $this;
    }

    public function orLtOrEq($field, $value)
    {
        $this->addCondition($field, $value, self::LT_OR_EQ, self::CHAIN_OR);

        return $this;
    }

    public function andLt($field, $value)
    {
        $this->addCondition($field, $value, self::LT, self::CHAIN_AND);

        return $this;
    }

    public function andLtOrEq($field, $value)
    {
        $this->addCondition($field, $value, self::LT_OR_EQ, self::CHAIN_AND);

        return $this;
    }

    public function orGt($field, $value)
    {
        $this->addCondition($field, $value, self::GT, self::CHAIN_OR);

        return $this;
    }

    public function orGtOrEq($field, $value)
    {
        $this->addCondition($field, $value, self::GT_OR_EQ, self::CHAIN_OR);

        return $this;
    }

    public function andGt($field, $value)
    {
        $this->addCondition($field, $value, self::GT, self::CHAIN_AND);

        return $this;
    }

    public function andGtOrEq($field, $value)
    {
        $this->addCondition($field, $value, self::GT_OR_EQ, self::CHAIN_AND);

        return $this;
    }

    public function andLike($field, $value)
    {
        $this->addCondition($field, $value, self::LIKE, self::CHAIN_AND);

        return $this;
    }

    public function andNotLike($field, $value)
    {
        $this->addCondition($field, $value, self::NOT_LIKE, self::CHAIN_AND);

        return $this;
    }

    public function orLike($field, $value)
    {
        $this->addCondition($field, $value, self::LIKE, self::CHAIN_OR);

        return $this;
    }

    public function orNotLike($field, $value)
    {
        $this->addCondition($field, $value, self::NOT_LIKE, self::CHAIN_OR);

        return $this;
    }

    // ------------- Generate -------------

    private function getConditionString($field, $cond, $value)
    {
        $str = "";

        switch ($cond) {
            case self::IS:
                $str = $field;
                break;
            case self::NOT:
                if ($value === null)
                    $str = $field . ' IS NOT ?';
                else
                    $str = $field . ' NOT';
                break;
            case self::LT:
                $str = $field . ' < ?';
                break;
            case self::GT:
                $str = $field . ' > ?';
                break;
            case self::LT_OR_EQ:
                $str = $field . ' <= ?';
                break;
            case self::GT_OR_EQ:
                $str = $field . ' >= ?';
                break;
            case self::BETWEEN:
                $str = $field . ' > ' . $value[0] . ' AND ' . $field . ' < ' . $value[1];
                break;
            case self::BETWEEN_INCLUDE:
                $str = $field . ' >= ' . $value[0] . ' AND ' . $field . ' <= ' . $value[1];
                break;
            case self::NOT_BETWEEN:
                $str = $field . ' < ' . $value[0] . ' OR ' . $field . ' > ' . $value[1];
                break;
            case self::NOT_BETWEEN_INCLUDE:
                $str = $field . ' <= ' . $value[0] . ' OR ' . $field . ' >= ' . $value[1];
                break;
            case self::LIKE:
                $str = $field . ' LIKE ?';
                break;
            case self::NOT_LIKE:
                $str = $field . ' NOT LIKE ?';
                break;
        }

        return $str;
    }

    /**
     * @param Select|Delete|Update $stm
     * @return Select
     */
    public function buildForSelectStatement($stm)
    {
        foreach ($this->conditions as $cond) {
            $value = $cond->formatValue();
            $field = $cond->formatField();

            $condStr = $this->getConditionString($field, $cond->cond, $value);

            if ($cond->cond === self::NOT && $value !== null)
                $value = [$value];

            if (in_array($cond->cond, [
                self::BETWEEN,
                self::BETWEEN_INCLUDE,
                self::NOT_BETWEEN,
                self::NOT_BETWEEN_INCLUDE
            ])) {
                if ($cond->chain === self::CHAIN_OR)
                    $stm->whereOr($condStr);
                else
                    $stm->where($condStr);

                continue;
            }

            if ($cond->chain === self::CHAIN_OR)
                $stm->whereOr($condStr, $value);
            else
                $stm->where($condStr, $value);
        }

        return $stm;
    }

}