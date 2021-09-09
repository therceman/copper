<?php


namespace Copper\Component\DB;


use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;
use Envms\FluentPDO\Queries\Delete;
use Envms\FluentPDO\Queries\Select;
use Envms\FluentPDO\Queries\Update;

class DBWhere
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
    const IN = 13;
    const NOT_IN = 14;

    const CHAIN_NULL = 20;
    const CHAIN_OR = 21;
    const CHAIN_AND = 22;

    /** @var DBWhereEntry[] */
    private $conditions;

    /**
     * DBWhere constructor.
     */
    public function __construct()
    {
        $this->conditions = [];
    }

    /**
     * @param string|string[] $field
     * @param string|int|float|array|null $value
     * @param int $cond
     * @param int $chain
     *
     * @return DBWhere
     */
    public static function createCondition($field, $value, int $cond, int $chain)
    {
        $self = new self();

        $self->addCondition($field, $value, $cond, $chain);

        return $self;
    }

    /**
     * Add Condition
     * @param string|string[] $field
     * @param string|int|float|null|array $value
     * @param int $cond
     * @param int $chain
     */
    private function addCondition($field, $value, int $cond, int $chain)
    {
        $this->conditions[] = new DBWhereEntry($field, $value, $cond, $chain);
    }

    /**
     * @return DBWhereEntry
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
     * @return DBWhere
     */
    public static function lt(string $field, $value)
    {
        return self::createCondition($field, $value, self::LT, self::CHAIN_NULL);
    }

    /**
     * Less Than OR Equal
     *
     * @param string $field
     * @param int|float $value
     *
     * @return DBWhere
     */
    public static function ltOrEq(string $field, $value)
    {
        return self::createCondition($field, $value, self::LT_OR_EQ, self::CHAIN_NULL);
    }

    /**
     * Greater Than
     *
     * @param string $field
     * @param int|float $value
     *
     * @return DBWhere
     */
    public static function gt(string $field, $value)
    {
        return self::createCondition($field, $value, self::GT, self::CHAIN_NULL);
    }

    /**
     * Greater Than OR Equal
     *
     * @param string $field
     * @param int|float $value
     *
     * @return DBWhere
     */
    public static function gtOrEq(string $field, $value)
    {
        return self::createCondition($field, $value, self::GT_OR_EQ, self::CHAIN_NULL);
    }

    /**
     * Is
     *
     * @param string $field
     * @param mixed $value
     *
     * @return DBWhere
     */
    public static function is(string $field, $value)
    {
        return self::createCondition($field, $value, self::IS, self::CHAIN_NULL);
    }

    /**
     * Not
     *
     * @param string $field
     * @param mixed $value
     *
     * @return DBWhere
     */
    public static function not(string $field, $value)
    {
        return self::createCondition($field, $value, self::NOT, self::CHAIN_NULL);
    }

    /**
     * Between
     * E.g. buyPrice > 20 OR buyPrice < 100
     *
     * @param string $field
     * @param int|float $start
     * @param int|float $end
     *
     * @return DBWhere
     */
    public static function between(string $field, $start, $end)
    {
        return self::createCondition($field, [$start, $end], self::BETWEEN, self::CHAIN_NULL);
    }

    /**
     * Between Include
     * E.g. buyPrice >= 20 OR buyPrice <= 100
     *
     * @param string $field
     * @param int|float $start
     * @param int|float $end
     *
     * @return DBWhere
     */
    public static function betweenInclude(string $field, $start, $end)
    {
        return self::createCondition($field, [$start, $end], self::BETWEEN_INCLUDE, self::CHAIN_NULL);
    }

    /**
     * Not Between
     * E.g. buyPrice < 20 OR buyPrice > 100
     *
     * @param string $field
     * @param int|float $start
     * @param int|float $end
     *
     * @return DBWhere
     */
    public static function notBetween(string $field, $start, $end)
    {
        return self::createCondition($field, [$start, $end], self::NOT_BETWEEN, self::CHAIN_NULL);
    }

    /**
     * NOT Between Include
     * E.g. buyPrice <= 20 OR buyPrice >= 100
     *
     * @param string $field
     * @param int|float $start
     * @param int|float $end
     *
     * @return DBWhere
     */
    public static function notBetweenInclude(string $field, $start, $end)
    {
        return self::createCondition($field, [$start, $end], self::NOT_BETWEEN_INCLUDE, self::CHAIN_NULL);
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
     * @param string|string[] $field
     * @param string|int|float $value
     *
     * @return DBWhere
     */
    public static function like($field, $value)
    {
        return self::createCondition($field, $value, self::LIKE, self::CHAIN_NULL);
    }

    /**
     * @param $value
     * @return string
     */
    private static function prepareLikeValue($value)
    {
        if (VarHandler::isArray($value)) {
            $value_list = [];

            foreach ($value as $v) {
                $value_list[] = '%' . DBService::escapeLikeStr($v) . '%';
            }

            $value = ArrayHandler::join($value_list, ' ');
        } else {
            $value = '%' . DBService::escapeLikeStr($value) . '%';
        }

        return $value;
    }

    /**
     * Shortcut for Like: %value%
     * <hr>
     * <code>
     * - likeAny('name', 'john');
     * - likeAny(['name', 'middle_name'], 'john')
     * - likeAny(['name', 'middle_name'], ['john', 'username'])
     * </code>
     * @param string|string[] $field
     * @param string|string[]|int|int[]|float|float[] $value
     *
     * @return DBWhere
     */
    public static function likeAny($field, $value)
    {
        $value = self::prepareLikeValue($value);

        return self::createCondition($field, $value, self::LIKE, self::CHAIN_NULL);
    }

    /**
     * Not Like
     *
     * (see like documentation for all cases)
     *
     * @param string|string[] $field
     * @param string|int|float $value
     *
     * @return DBWhere
     */
    public static function notLike($field, $value)
    {
        return self::createCondition($field, $value, self::NOT_LIKE, self::CHAIN_NULL);
    }

    /**
     * @param string $field
     * @param int[]|string[] $value
     *
     * @return DBWhere
     */
    public static function in(string $field, array $value)
    {
        return self::createCondition($field, $value, self::IN, self::CHAIN_NULL);
    }

    /**
     * @param string $field
     * @param int[]|string[] $value
     *
     * @return DBWhere
     */
    public static function notIn(string $field, array $value)
    {
        return self::createCondition($field, $value, self::NOT_IN, self::CHAIN_NULL);
    }

    // ------------ Shortcuts ---------------

    public static function notNull($field)
    {
        return self::not($field, null);
    }

    public static function isNull($field)
    {
        return self::is($field, null);
    }

    /**
     * Is Like
     *
     * (see like for documentation)
     *
     * @param string|string[] $field
     * @param string|int|float $value
     *
     * @return DBWhere
     */
    public static function isLike($field, $value)
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

    /**
     * @param string|string[] $field
     * @param mixed $value
     *
     * @return $this
     */
    public function andLike($field, $value)
    {
        $this->addCondition($field, $value, self::LIKE, self::CHAIN_AND);

        return $this;
    }

    /**
     * Shortcut for andLike: %value%
     * <hr>
     * <code>
     * - andLike('name', 'john');
     * - andLike(['name', 'middle_name'], 'john')
     * - andLike(['name', 'middle_name'], ['john', 'johny'])
     * </code>
     * @param string|string[] $field
     * @param string|string[]|int|int[]|float|float[] $value
     *
     * @return $this
     */
    public function andLikeAny($field, $value)
    {
        $value = $this::prepareLikeValue($value);

        $this->addCondition($field, $value, self::LIKE, self::CHAIN_AND);

        return $this;
    }

    /**
     * @param string|string[] $field
     * @param mixed $value
     *
     * @return $this
     */
    public function andNotLike($field, $value)
    {
        $this->addCondition($field, $value, self::NOT_LIKE, self::CHAIN_AND);

        return $this;
    }

    /**
     * @param string|string[] $field
     * @param mixed $value
     *
     * @return $this
     */
    public function orLike($field, $value)
    {
        $this->addCondition($field, $value, self::LIKE, self::CHAIN_OR);

        return $this;
    }

    /**
     * Shortcut for orLike: %value%
     * <hr>
     * <code>
     * - orLikeAny('name', 'john');
     * - orLikeAny(['name', 'middle_name'], 'john')
     * - orLikeAny(['name', 'middle_name'], ['john', 'johny'])
     * </code>
     * @param string|string[] $field
     * @param string|string[]|int|int[]|float|float[] $value
     *
     * @return $this
     */
    public function orLikeAny($field, $value)
    {
        $value = $this::prepareLikeValue($value);

        $this->addCondition($field, $value, self::LIKE, self::CHAIN_OR);

        return $this;
    }

    /**
     * @param string|string[] $field
     * @param mixed $value
     *
     * @return $this
     */
    public function orNotLike($field, $value)
    {
        $this->addCondition($field, $value, self::NOT_LIKE, self::CHAIN_OR);

        return $this;
    }

    public function andIn($field, $value)
    {
        $this->addCondition($field, $value, self::IN, self::CHAIN_AND);

        return $this;
    }

    public function andNotIn($field, $value)
    {
        $this->addCondition($field, $value, self::NOT_IN, self::CHAIN_AND);

        return $this;
    }

    public function orIn($field, $value)
    {
        $this->addCondition($field, $value, self::IN, self::CHAIN_OR);

        return $this;
    }

    public function orNotIn($field, $value)
    {
        $this->addCondition($field, $value, self::NOT_IN, self::CHAIN_OR);

        return $this;
    }

    // ------------- Generate -------------

    /**
     * @param string|string[] $field
     * @param int $cond
     * @param mixed $value
     *
     * @return string
     */
    private function getConditionString($field, int $cond, $value)
    {
        $str = "";

        switch ($cond) {
            case self::IS:
            case self::IN:
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
                $value = DBService::escapeStr($value);
                $str = (VarHandler::isArray($field))
                    ? '(' . ArrayHandler::join($field, ' LIKE \'' . $value . '\' OR ') . ' LIKE ?)'
                    : $field . ' LIKE ?';
                break;
            case self::NOT_LIKE:
                $str = (VarHandler::isArray($field))
                    ? 'CONCAT_WS(\'\', ' . ArrayHandler::join($field) . ') NOT LIKE ?'
                    : $field . ' NOT LIKE ?';
                break;
            case self::NOT_IN:
                $str = $field . ' NOT';
                break;
        }

        return $str;
    }

    /**
     * @param DBColumnMod[]|null $columnMods
     * @param DBWhereEntry $cond
     * @param string $condStr
     * @param mixed $value
     *
     * @return false|mixed|string
     */
    private function getHavingCondStrByColumnMod($columnMods, DBWhereEntry $cond, string $condStr, $value)
    {
        $condFieldList = VarHandler::isArray($cond->field) ? $cond->field : [$cond->field];

        if ($columnMods === null)
            return false;

        $columnModFound = false;
        foreach ($columnMods as $mod) {
            foreach ($condFieldList as $condField) {
                if ($mod->getColumn() === DBModel::formatFieldName($condField, true))
                    $columnModFound = true;
            }
        }

        if ($columnModFound === false)
            return false;

        // TODO test needed
        $condStr = StringHandler::has($condStr, '?')
            ? StringHandler::replace($condStr, '?', $value)
            : $condStr;

        return $condStr;
    }

    /**
     * @param Select|Delete|Update $stm
     * @param DBColumnMod[]|null $columnMods
     * @return Select
     */
    public function buildForStatement($stm, $columnMods = null)
    {
        foreach ($this->conditions as $cond) {
            $value = $cond->formatValue();
            $field = $cond->formatField();

            $condStr = $this->getConditionString($field, $cond->cond, $value);

            if ($cond->cond === self::NOT && $value !== null && VarHandler::isArray($value) === false)
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

            if (VarHandler::isArray($value) && count($value) === 0 && in_array($cond->cond, [self::NOT, self::NOT_IN]))
                $condStr = '1 = 1';

            if (VarHandler::isArray($value) && count($value) === 0 && in_array($cond->cond, [self::IS, self::IN]))
                $condStr = '1 = 2';


            $havingCondStr = self::getHavingCondStrByColumnMod($columnMods, $cond, $condStr, $value);

            if ($havingCondStr !== false) {
                $stm->having($havingCondStr);
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