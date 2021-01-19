<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;

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

    public function getFieldNames()
    {
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

    /**
     * @param AbstractEntity $entity
     * @param array|false $onlySelectedFields
     * @return array
     */
    public function getFieldValuesFromEntity(AbstractEntity $entity, $onlySelectedFields = false)
    {
        $entityFields = $entity->toArray();

        $fieldValues = [];

        foreach ($this->fields as $field) {
            if (array_key_exists($field->name, $entityFields) === false)
                continue;

            if ($onlySelectedFields !== false && array_search($field->name, $onlySelectedFields) === false)
                continue;

            // value processing should be separated
            $value = $entityFields[$field->name];

            if (is_string($value))
                $value = "'$value'";

            if ($value === null && in_array($field->type, [$field::DATETIME, $field::DATE]) && $field->null !== true)
                $value = 'now()';

            if ($value === null && $field->type === $field::YEAR && $field->null !== true)
                $value = 'YEAR(CURDATE())';

            if ($value === null)
                $value = 'NULL';

            $fieldValues[$field->name] = $value;
        }

        return $fieldValues;
    }
}