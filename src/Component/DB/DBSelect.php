<?php


namespace Copper\Component\DB;


use Copper\Handler\ArrayHandler;
use Copper\Handler\VarHandler;

/**
 * Class DBSelect
 * @package Copper\Component\DB
 */
class DBSelect
{
    /** @var DBWhere|null */
    private $where;
    /** @var string[]|null */
    private $columns;
    /** @var DBColumnMod[]|null */
    private $columnMods;
    /** @var string[]|null */
    private $ignoredColumns;
    /** @var DBOrder|null */
    private $order;
    /** @var int|null */
    private $limit;
    /** @var int|null */
    private $offset;
    /** @var string|null */
    private $group;
    /** @var DBOutput|null */
    private $output;

    public function __construct()
    {
        $this->where = null;
        $this->columns = null;
        $this->columnMods = null;
        $this->ignoredColumns = null;
        $this->order = null;
        $this->limit = null;
        $this->offset = null;
        $this->group = null;
        $this->output = null;
    }

    /**
     * @param DBWhere|null $where
     *
     * @return DBSelect
     */
    public static function where($where)
    {
        $params = new DBSelect();

        return $params->setWhere($where);
    }

    /**
     * @param string|string[]|null $columns
     *
     * @return DBSelect
     */
    public static function columns($columns): DBSelect
    {
        $params = new DBSelect();

        return $params->setColumns($columns);
    }

    /**
     * Set column modification
     * <hr>
     * <code>
     * // Using statement with parameters
     * - columnMod('price', DBColumnMod::statement('$1 - ($1 * $2) / 100', ['price', 10]);
     * # Transforms to: SELECT `price` - (`price` * 10) / 100 as `price` FROM ...
     *
     * // Using subPerc (as value)
     * - columnMod('price', DBColumnMod::subPerc(10);
     * # Transforms to: SELECT `price` - (`price` * 10) / 100 as `price` FROM ...
     *
     * // Using subPerc (as column)
     * - columnMod('price', DBColumnMod::subPerc('discount_perc');
     * # Transforms to: SELECT `price` - (`price` * `discount_perc`) / 100 as `price` ...
     *
     * // Chaining
     * - columnMod('price', DBColumnMod::subPerc(10)->andAddPerc(5));
     * # Transforms to: SELECT `price` - (`price` * 10) / 100 + (`price` * 5) / 100 as `price`
     * </code>
     * @param string $column
     * @param DBColumnMod|null $mod
     *
     * @return DBSelect
     */
    public static function columnMod(string $column, $mod): DBSelect
    {
        $params = new DBSelect();

        return $params->setColumnMod($column, $mod);
    }

    /**
     * @param string|string[]|null $columns
     *
     * @return DBSelect
     */
    public static function ignoreColumns($columns): DBSelect
    {
        $params = new DBSelect();

        return $params->setIgnoredColumns($columns);
    }

    /**
     * @param DBOrder|null $order
     *
     * @return DBSelect
     */
    public static function order($order): DBSelect
    {
        $params = new DBSelect();

        return $params->setOrder($order);
    }

    /**
     * @param int|null $limit
     *
     * @return DBSelect
     */
    public static function limit($limit): DBSelect
    {
        $params = new DBSelect();

        return $params->setLimit($limit);
    }

    /**
     * @param int|null $offset
     *
     * @return DBSelect
     */
    public static function offset($offset): DBSelect
    {
        $params = new DBSelect();

        return $params->setOffset($offset);
    }

    /**
     * @param string|null $group
     *
     * @return DBSelect
     */
    public static function group($group): DBSelect
    {
        $params = new DBSelect();

        return $params->setGroup($group);
    }

    /**
     * @param DBOutput|null $output
     *
     * @return DBSelect
     */
    public static function output($output): DBSelect
    {
        $params = new DBSelect();

        return $params->setOutput($output);
    }

    /**
     * @param \Closure $closure
     * @return DBSelect
     * @see DBOutput::map()
     */
    public static function outputMap(\Closure $closure)
    {
        $params = new DBSelect();

        return $params->setOutput(DBOutput::map($closure));
    }

    /**
     * @param string|null $index
     *
     * @return DBSelect
     */
    public static function outputIndex(?string $index = DBModel::ID)
    {
        $params = new DBSelect();

        return $params->setOutput(DBOutput::index($index));
    }

    /**
     * @param string|null $field
     *
     * @return DBSelect
     */
    public static function outputList(?string $field = DBModel::ID)
    {
        $params = new DBSelect();

        return $params->setOutput(DBOutput::listOutput($field));
    }

    /**
     * @param $columns
     * @return mixed
     */
    private function prepareColumns($columns)
    {
        if ($columns !== null) {
            $columns = (VarHandler::isArray($columns) === false) ? [$columns] : $columns;

            $columns = ArrayHandler::map($columns, function ($column) {
                return '`' . DBModel::formatFieldName($column) . '`';
            });
        }

        return $columns;
    }

    /**
     * Set column modification
     * @param string $column
     * @param DBColumnMod|null $mod
     *
     * @return DBSelect
     *
     * @see columnMod for example
     */
    public function setColumnMod(string $column, $mod): DBSelect
    {
        if ($mod !== null)
            $this->columnMods[] = $mod->setColumn($column);

        return $this;
    }

    /**
     * @param string $column
     * @param DBMatchColumnMod|null $dbMatchColumnMod
     * @return DBSelect
     */
    public function setMatchColumnMod(string $column, ?DBMatchColumnMod $dbMatchColumnMod): DBSelect
    {
        if ($dbMatchColumnMod !== null)
            $this->columnMods[] = $dbMatchColumnMod->setColumn($column);

        return $this;
    }

    /**
     * @param string|string[]|null $columns
     *
     * @return DBSelect
     */
    public function setColumns($columns): DBSelect
    {
        $this->columns = $this->prepareColumns($columns);

        return $this;
    }

    /**
     * @param string|string[]|null $fields
     *
     * @return DBSelect
     */
    public function setOutputFields($fields): DBSelect
    {
        if ($this->output === null)
            $this->output = DBOutput::fields($fields);
        else
            $this->output->setFields($fields);

        return $this;
    }

    /**
     * @param string|null $index
     *
     * @return DBSelect
     */
    public function setOutputIndex(?string $index = DBModel::ID)
    {
        if ($this->output === null)
            $this->output = DBOutput::index($index);
        else
            $this->output->setIndex($index);

        return $this;
    }

    /**
     * @param \Closure $closure
     * @return DBSelect
     * @see DBOutput::map()
     */
    public function setOutputMap(\Closure $closure)
    {
        if ($this->output === null)
            $this->output = DBOutput::map($closure);
        else
            $this->output->setMap($closure);

        return $this;
    }

    /**
     * @param string|null $field
     * @return DBSelect
     * @see DBOutput::listOutput()
     */
    public function setOutputList(?string $field)
    {
        if ($this->output === null)
            $this->output = DBOutput::listOutput($field);
        else
            $this->output->setListOutput($field);

        return $this;
    }

    /**
     * @param string|string[]|null $columns
     *
     * @return DBSelect
     */
    public function setIgnoredColumns($columns): DBSelect
    {
        $this->ignoredColumns = $this->prepareColumns($columns);

        return $this;
    }

    /**
     * @param DBWhere|null $where
     *
     * @return DBSelect
     */
    public function setWhere($where): DBSelect
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @param DBOrder|null $order
     * @return DBSelect
     */
    public function setOrder($order): DBSelect
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param $field
     * @return DBSelect
     */
    public function setOrderDESC($field): DBSelect
    {
        $this->order = DBOrder::DESC($field);
        return $this;
    }

    /**
     * @param $field
     * @return DBSelect
     */
    public function setOrderASC($field): DBSelect
    {
        $this->order = DBOrder::ASC($field);
        return $this;
    }

    /**
     * @param int|null $limit
     * @return DBSelect
     */
    public function setLimit($limit): DBSelect
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int|null $offset
     * @return DBSelect
     */
    public function setOffset($offset): DBSelect
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param string|null $group
     * @return DBSelect
     */
    public function setGroup($group): DBSelect
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param DBOutput|null $output
     * @return DBSelect
     */
    public function setOutput($output): DBSelect
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return DBWhere|null
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @return string[]|null
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return DBColumnMod[]|null
     */
    public function getColumnMods()
    {
        return $this->columnMods;
    }

    /**
     * @return string[]|null
     */
    public function getIgnoredColumns()
    {
        return $this->ignoredColumns;
    }

    /**
     * @return DBOrder|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return string|null
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return DBOutput|null
     */
    public function getOutput()
    {
        return $this->output;
    }

}