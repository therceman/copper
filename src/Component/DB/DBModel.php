<?php


namespace Copper\Component\DB;


abstract class DBModel
{
    /** @var string */
    public $tableName = '';

    /** @var DBModelField[] */
    public $fields = [];

    abstract function setTableName();

    abstract function setFields();

    public function __construct()
    {
        $this->setTableName();
        $this->setFields();
    }

    /**
     * Add Field to Model
     *
     * @param string $name (optional)Name
     * @param bool|string $type (optional)Type
     * @param bool|int|array $length (optional) Length
     *
     * @return DBModelField
     */
    public function field(string $name, $type = false, $length = false)
    {
        $field = new DBModelField($name, $type, $length);

        $fields[] = $field;

        return $field;
    }
}