<?php


namespace Copper\Component\DB;


use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;
use Copper\Kernel;

/**
 * Class DBColumnMod
 * @package Copper\Component\DB
 */
class DBColumnMod
{
    const PARAM_KEY = '$';

    /**
     * @var DBColumnModAction[]
     */
    private $actions;

    /**
     * @var string|null
     */
    private $column;

    /**
     * @var string|null
     */
    protected $statement;

    /**
     * @var int|null
     */
    private $roundResultDec;

    /**
     * @var array
     */
    private $ifNullDefaultValueList;

    /**
     * DBColumnMod constructor.
     * @param string|null $statement
     * @param string|null $params
     */
    public function __construct($statement = null, $params = null)
    {
        $this->actions = [];
        $this->statement = null;
        $this->ifNullDefaultValueList = [];

        $this->roundResultDec = null;

        if ($statement !== null && $params !== null)
            $this->statement = $this->createStatement($statement, $params);
    }

    /**
     * @param string|null $column
     * @return $this
     */
    public function setColumn($column)
    {
        $this->column = '`' . DBModel::formatFieldName($column) . '`';

        return $this;
    }

    /**
     * @param $column
     * @param $value
     */
    public function setIfNullDefaultValue($column, $value)
    {
        $this->ifNullDefaultValueList[$column] = $value;

        return $this;
    }

    /**
     * @param DBColumnModAction|null $action
     */
    public function addAction($action)
    {
        if ($action === null)
            return;

        $this->actions[] = $action;
    }

    /**
     * @return string|null
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return string
     */
    private function craftStatementFromActions()
    {
        $statement = $this->column;

        $col = StringHandler::delete($this->column, '`');

        foreach ($this->actions as $action) {
            $type = $action->getType();
            $val = $action->getValue();

            if ($type === DBColumnModAction::ADD_PERC && $action->isChained() === false)
                $statement = $statement . $this->createStatement(' + ($1 * $2) / 100', [$col, $val]);

            if ($type === DBColumnModAction::ADD_PERC && $action->isChained())
                $statement = '(' . $statement . $this->createStatement(') + ((' . $statement . ') * $1) / 100', [$val]);

            if ($type === DBColumnModAction::SUB_PERC && $action->isChained() === false)
                $statement = $statement . $this->createStatement(' - ($1 * $2) / 100', [$col, $val]);

            if ($type === DBColumnModAction::SUB_PERC && $action->isChained())
                $statement = '(' . $statement . $this->createStatement(') - ((' . $statement . ') * $1) / 100', [$val]);

            if ($type === DBColumnModAction::ADD && $action->isChained())
                $statement = $statement . $this->createStatement(' + $1', [$val]);

            if ($type === DBColumnModAction::SUB && $action->isChained())
                $statement = $statement . $this->createStatement(' - $1', [$val]);

            if ($type === DBColumnModAction::DIV && $action->isChained())
                $statement = $statement . $this->createStatement(' / $1', [$val]);

            if ($type === DBColumnModAction::MUL && $action->isChained())
                $statement = $statement . $this->createStatement(' * $1', [$val]);
        }

        $statement = '(' . $statement . ')';

        if ($this->roundResultDec !== null)
            $statement = 'ROUND(' . $statement . ',' . $this->roundResultDec . ')';

        return $statement . ' as ' . $this->column;
    }

    /**
     * @return string|null
     */
    public function getCraftedStatement()
    {
        if ($this->statement !== null)
            return $this->statement . ' as ' . $this->column;

        if (ArrayHandler::count($this->actions) > 0)
            return $this->craftStatementFromActions();

        return $this->column;
    }

    /**
     * @param $value
     * @return int
     */
    private function getIfNullDefaultValue($value)
    {
        $value = StringHandler::replace($value, '`', '');

        if (ArrayHandler::hasKey($this->ifNullDefaultValueList, $value) === false)
            return DBModel::formatNumber(Kernel::getDb()->config->ifNullDefaultValue);

        return DBModel::formatNumber($this->ifNullDefaultValueList[$value]);
    }

    /**
     * @param $value
     * @return string
     */
    private function prepareParamValue($value)
    {
        $ifNullDefaultValue = self::getIfNullDefaultValue($value);

        if (is_string($value))
            $value = 'IFNULL(' . DBModel::formatFieldName($value, true) . ', ' . $ifNullDefaultValue . ')';

        return $value;
    }

    /**
     * @param $statement
     * @param $params
     * @return string
     */
    protected function createStatement($statement, $params)
    {
        foreach ($params as $key => $value) {
            if (StringHandler::has($key, self::PARAM_KEY) === false)
                $key = self::PARAM_KEY . ((int)$key + 1);

            $statement = StringHandler::replace($statement, $key, $this->prepareParamValue($value));
        }

        return $statement;
    }

    /**
     * Create statement with parameters. Each parameter is marked by key with dollar sign ($1, $2, ...)
     * <hr>
     * <code>
     * - statement('$1 - ($1 * $2) / 100', ['price', 'price_discount_perc'])
     * # transform to: `price` - (`price` * `price_discount_perc`) / 100
     * </code>
     * @param string $statement
     * @param array $params
     * @return DBColumnMod
     */
    public static function statement(string $statement, array $params)
    {
        return new self($statement, $params);
    }

    /**
     * Subtract
     * <hr>
     * <code>
     * - sub(10)                    // subtract 10
     * - sub('price_discount_perc') // subtract column `price_discount_perc` value
     * </code>
     * @param int|float|string $value
     *
     * @return DBColumnMod
     */
    public static function sub($value)
    {
        $self = new self();

        $self->addAction(new DBColumnModAction(DBColumnModAction::SUB, $value, true));

        return $self;
    }

    /**
     * Subtract
     *
     * @param $value
     *
     * @return DBColumnMod
     *
     * @see sub for example
     */
    public function andSub($value)
    {
        $this->addAction(new DBColumnModAction(DBColumnModAction::SUB, $value, true));

        return $this;
    }

    /**
     * Add
     * <hr>
     * <code>
     * - add(10)                    // add 10
     * - add('price_discount_perc') // add column `price_discount_perc` value
     * </code>
     * @param int|float|string $value
     *
     * @return DBColumnMod
     */
    public static function add($value)
    {
        $self = new self();

        $self->addAction(new DBColumnModAction(DBColumnModAction::ADD, $value, true));

        return $self;
    }

    /**
     * Add
     *
     * @param $value
     *
     * @return DBColumnMod
     *
     * @see add for example
     */
    public function andAdd($value)
    {
        $this->addAction(new DBColumnModAction(DBColumnModAction::ADD, $value, true));

        return $this;
    }

    /**
     * Divide
     * <hr>
     * <code>
     * - div(10)                    // divide by 10
     * - div('price_discount_perc') // divide by column `price_discount_perc` value
     * </code>
     * @param int|float|string $value
     *
     * @return DBColumnMod
     */
    public static function div($value)
    {
        $self = new self();

        $self->addAction(new DBColumnModAction(DBColumnModAction::DIV, $value, true));

        return $self;
    }

    /**
     * Divide
     *
     * @param $value
     *
     * @return DBColumnMod
     *
     * @see div for example
     */
    public function andDiv($value)
    {
        $this->addAction(new DBColumnModAction(DBColumnModAction::DIV, $value, true));

        return $this;
    }

    /**
     * Multiply
     * <hr>
     * <code>
     * - mul(10)                    // multiply by 10
     * - mul('price_discount_perc') // multiply by column `price_discount_perc` value
     * </code>
     * @param int|float|string $value
     *
     * @return DBColumnMod
     */
    public static function mul($value)
    {
        $self = new self();

        $self->addAction(new DBColumnModAction(DBColumnModAction::MUL, $value, true));

        return $self;
    }

    /**
     * Multiply
     *
     * @param $value
     *
     * @return DBColumnMod
     *
     * @see mul for example
     */
    public function andMul($value)
    {
        $this->addAction(new DBColumnModAction(DBColumnModAction::MUL, $value, true));

        return $this;
    }

    /**
     * Subtract Percents
     * <hr>
     * <code>
     * - subPerc(10)
     * # sub perc: A - (A * 10) / 100
     *
     * - subPerc('price_discount_perc')
     * # sub column perc: A - (A * `price_discount_perc`) / 100
     *
     * - subPerc(10)->andSubPerc(20)
     * # multi sub perc: A - (A * 10) / 100 - (A * 20) / 100
     *
     * - subPerc(10)->andSubPerc(20, true)
     * # multi sub chained perc: (A - (A * 10) / 100) - ((A - (A * 10) / 100) * 20) / 100
     * </code>
     * @param int|float|string $value
     * @param bool $chained [optional] = true
     * @return DBColumnMod
     */
    public static function subPerc($value, $chained = false)
    {
        $self = new self();

        $self->addAction(new DBColumnModAction(DBColumnModAction::SUB_PERC, $value, $chained));

        return $self;
    }

    /**
     * Subtract Percents
     *
     * @param int|float|string $value
     * @param bool $chained
     *
     * @return $this
     *
     * @see subPerc for example
     */
    public function andSubPerc($value, $chained = false)
    {
        $this->addAction(new DBColumnModAction(DBColumnModAction::SUB_PERC, $value, $chained));

        return $this;
    }

    /**
     * Add Percents
     * <hr>
     * <code>
     * - addPerc(10)
     * # add perc: A + (A * 10) / 100
     *
     * - addPerc('price_discount_perc')
     * # add perc column: A + (A * `price_discount_perc`) / 100
     *
     * - addPerc(10)->andAddPerc(20)
     * # multi add perc: A + (A * 10) / 100 + (A * 20) / 100
     *
     * - addPerc(10)->andaddPerc(20, true)
     * # multi add chained perc: (A + (A * 10) / 100) + ((A + (A * 10) / 100) * 20) / 100
     * </code>
     * @param int|float|string $value
     *
     * @return DBColumnMod
     */
    public static function addPerc($value, $chained = false)
    {
        $self = new self();

        $self->addAction(new DBColumnModAction(DBColumnModAction::ADD_PERC, $value, $chained));

        return $self;
    }


    /**
     * Add Percents
     *
     * @param int|float|string $value
     *
     * @return $this
     *
     * @see addPerc for example
     */
    public function andAddPerc($value, $chained = false)
    {
        $this->addAction(new DBColumnModAction(DBColumnModAction::ADD_PERC, $value, $chained));

        return $this;
    }

    /**
     * Rounds result
     * <hr>
     * <code>
     * - roundResult(2) // Rounds to 2 decimals. E.g. 1.236 => 1.24
     * - roundResult(0) // Rounds to 0 decimals. E.g. 1.236 => 1
     * </code>
     *
     * @param int|null $dec
     *
     * @return $this
     */
    public function roundResult($dec)
    {
        $this->roundResultDec = $dec;

        return $this;
    }
}