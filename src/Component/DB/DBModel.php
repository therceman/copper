<?php


namespace Copper\Component\DB;


abstract class DBModel
{
    /** @var string */
    public $tableName = '';

    /** @var DBModelField[] */
    public $fields = [];

    abstract function getTableName();

    abstract function setFields();

    public function __construct()
    {
        $this->tableName = $this->getTableName();
        $this->setFields();
    }

    public function getFieldNames() {
        $names = [];

        foreach ($this->fields as $field) {
            $names[] = $field->name;
        }

        return $names;
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

        $this->fields[] = $field;

        return $field;
    }
}