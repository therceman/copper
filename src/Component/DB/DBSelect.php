<?php


namespace Copper\Component\DB;


use Copper\Handler\ArrayHandler;

class DBSelect
{
    /** @var DBWhere|null */
    private $where;
    /** @var string[]|null */
    private $columns;
    /** @var DBOrder|null */
    private $order;
    /** @var integer|null */
    private $limit;
    /** @var integer|null */
    private $offset;
    /** @var string|null */
    private $group;
    /** @var DBOutput|null */
    private $output;

    public function __construct()
    {
        $this->where = null;
        $this->columns = null;
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
     * @param string|string[]|null $columns
     *
     * @return DBSelect
     */
    public function setColumns($columns): DBSelect
    {
        if ($columns !== null) {
            $columns = (is_array($columns) === false) ? [$columns] : $columns;

            $columns = ArrayHandler::map($columns, function ($column) {
                return '`' . DBModel::formatFieldName($column) . '`';
            });
        }

        $this->columns = $columns;

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