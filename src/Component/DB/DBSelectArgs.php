<?php


namespace Copper\Component\DB;


class DBSelectArgs
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

    public function __construct()
    {
        $this->where = null;
        $this->columns = null;
        $this->order = null;
        $this->limit = null;
        $this->offset = null;
        $this->group = null;
    }

    /**
     * @param DBWhere $where
     *
     * @return DBSelectArgs
     */
    public static function where(DBWhere $where)
    {
        $params = new DBSelectArgs();

        return $params->setWhere($where);
    }

    /**
     * @param string|string[] $columns
     *
     * @return DBSelectArgs
     */
    public static function columns($columns): DBSelectArgs
    {
        $params = new DBSelectArgs();

        return $params->setColumns($columns);
    }

    /**
     * @param DBOrder $order
     *
     * @return DBSelectArgs
     */
    public static function order(DBOrder $order): DBSelectArgs
    {
        $params = new DBSelectArgs();

        return $params->setOrder($order);
    }

    /**
     * @param int $limit
     *
     * @return DBSelectArgs
     */
    public static function limit(int $limit): DBSelectArgs
    {
        $params = new DBSelectArgs();

        return $params->setLimit($limit);
    }

    /**
     * @param int $offset
     *
     * @return DBSelectArgs
     */
    public static function offset(int $offset): DBSelectArgs
    {
        $params = new DBSelectArgs();

        return $params->setOffset($offset);
    }

    /**
     * @param string $group
     *
     * @return DBSelectArgs
     */
    public static function group(string $group): DBSelectArgs
    {
        $params = new DBSelectArgs();

        return $params->setGroup($group);
    }

    /**
     * @param string|string[] $columns
     *
     * @return DBSelectArgs
     */
    public function setColumns($columns): DBSelectArgs
    {
        $this->columns = (is_array($columns) === false) ? [$columns] : $columns;
        return $this;
    }

    /**
     * @param DBWhere|null $where
     *
     * @return DBSelectArgs
     */
    public function setWhere(DBWhere $where): DBSelectArgs
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @param DBOrder $order
     * @return DBSelectArgs
     */
    public function setOrder(DBOrder $order): DBSelectArgs
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param int|null $limit
     * @return DBSelectArgs
     */
    public function setLimit(int $limit): DBSelectArgs
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int|null $offset
     * @return DBSelectArgs
     */
    public function setOffset(int $offset): DBSelectArgs
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param string|null $group
     * @return DBSelectArgs
     */
    public function setGroup(string $group): DBSelectArgs
    {
        $this->group = $group;
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
}