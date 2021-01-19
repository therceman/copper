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
     * Format Field Values for Update/Insert
     *
     * @param array $fieldValues
     * @param bool $escapeStrings
     * @param bool $removeNullFields
     *
     * @return array
     */
    public function formatFieldValues(array $fieldValues, $removeNullFields = true, $escapeStrings = false)
    {
        $formattedValues = [];

        foreach ($this->fields as $field) {
            if (array_key_exists($field->name, $fieldValues) === false)
                continue;

            $value = $fieldValues[$field->name];

            if ($value === null && in_array($field->type, [$field::DATETIME, $field::DATE]) && $field->null !== true)
                $value = DBHandler::datetime();

            if ($value === null && $field->type === $field::YEAR && $field->null !== true)
                $value = DBHandler::year();

            if (is_string($value) && $escapeStrings === true)
                $value = DBHandler::escape($value);

            if ($value === null && $removeNullFields === true)
                continue;

            if ($value === null)
                $value = DBHandler::null();

            $formattedValues[$field->name] = $value;
        }

        return $formattedValues;
    }

    /**
     * @param AbstractEntity $entity
     * @param array|false $onlySelectedFields
     *
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

            $fieldValues[$field->name] = $entityFields[$field->name];
        }

        return $fieldValues;
    }
}