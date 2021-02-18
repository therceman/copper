<?php


namespace Copper\Component\DB;


class DBSelectParams
{
    /** @var DBCondition|null */
    private $condition;
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

    public function __construct()
    {
        $this->condition = null;
        $this->columns = null;
        $this->order = null;
        $this->limit = null;
        $this->offset = null;
        $this->group = null;
    }

    /**
     * @param DBCondition $condition
     *
     * @return DBSelectParams
     */
    public static function condition(DBCondition $condition)
    {
        $params = new DBSelectParams();

        return $params->setCondition($condition);
    }

    /**
     * @param string|string[] $columns
     *
     * @return DBSelectParams
     */
    public static function columns($columns): DBSelectParams
    {
        $params = new DBSelectParams();

        return $params->setColumns($columns);
    }

    /**
     * @param DBOrder $order
     *
     * @return DBSelectParams
     */
    public static function order(DBOrder $order): DBSelectParams
    {
        $params = new DBSelectParams();

        return $params->setOrder($order);
    }

    /**
     * @param int $limit
     *
     * @return DBSelectParams
     */
    public static function limit(int $limit): DBSelectParams
    {
        $params = new DBSelectParams();

        return $params->setLimit($limit);
    }

    /**
     * @param int $offset
     *
     * @return DBSelectParams
     */
    public static function offset(int $offset): DBSelectParams
    {
        $params = new DBSelectParams();

        return $params->setOffset($offset);
    }

    /**
     * @param string $group
     *
     * @return DBSelectParams
     */
    public static function group(string $group): DBSelectParams
    {
        $params = new DBSelectParams();

        return $params->setGroup($group);
    }

    /**
     * @param string|string[] $columns
     *
     * @return DBSelectParams
     */
    public function setColumns($columns): DBSelectParams
    {
        $this->columns = (is_array($columns) === false) ? [$columns] : $columns;
        return $this;
    }

    /**
     * @param DBCondition|null $condition
     *
     * @return DBSelectParams
     */
    public function setCondition(DBCondition $condition): DBSelectParams
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @param DBOrder $order
     * @return DBSelectParams
     */
    public function setOrder(DBOrder $order): DBSelectParams
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param int|null $limit
     * @return DBSelectParams
     */
    public function setLimit(int $limit): DBSelectParams
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int|null $offset
     * @return DBSelectParams
     */
    public function setOffset(int $offset): DBSelectParams
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param string|null $group
     * @return DBSelectParams
     */
    public function setGroup(string $group): DBSelectParams
    {
        $this->group = $group;
        return $this;
    }
}