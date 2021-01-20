<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;
use Copper\FunctionResponse;

abstract class DBModel
{
    const ID = 'id';

    // State Fields Support

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const REMOVED_AT = 'removed_at';
    const ENABLED = 'enabled';

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

    /**
     * @param $name
     *
     * @return DBModelField|null
     */
    public function getFieldByName($name)
    {
        $foundField = null;

        foreach ($this->fields as $field) {
            if ($field->name === $name)
                $foundField = $field;
        }

        return $foundField;
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
     * Check if model has required fields, else return error with missing fields list
     *
     * @param array $fieldNames
     *
     * @return FunctionResponse
     */
    public function hasFields(array $fieldNames)
    {
        $response = new FunctionResponse();

        $missingFields = [];

        foreach ($fieldNames as $field) {
            if (array_search($field, $this->getFieldNames()) === false)
                $missingFields[] = $field;
        }

        if (count($missingFields) > 0)
            return $response->fail('Model has missing fields', $missingFields);

        return $response->ok();
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

    public function addStateFields($enabledByDefault = false)
    {
        $this->field(self::CREATED_AT, DBModelField::DATETIME)->currentTimestampByDefault();
        $this->field(self::UPDATED_AT, DBModelField::DATETIME)->currentTimestampOnUpdate()->nullByDefault();
        $this->field(self::REMOVED_AT, DBModelField::DATETIME)->nullByDefault();
        $this->field(self::ENABLED, DBModelField::BOOLEAN)->default(intval($enabledByDefault));
    }

    /**
     * @param $name
     *
     * @return string
     */
    public static function formatFieldName($name)
    {
        return preg_replace("/[^a-zA-Z0-9_]+/", "", $name);
    }

    /**
     * @param $number
     *
     * @return float
     */
    public static function formatNumber($number)
    {
        return floatval(preg_replace('/[^.0-9]/', '', $number));
    }

    /**
     * Format Field Values for Update/Insert
     *
     * @param array $fieldValues
     * @param bool $removeNullFields
     *
     * @return array
     */
    public function formatFieldValues(array $fieldValues, $removeNullFields = true)
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

            if ($value === null && in_array($field->default, [$field::DEFAULT_NONE, $field::DEFAULT_CURRENT_TIMESTAMP, $field::DEFAULT_NONE]) === false && $field->null !== true)
                $value = $field->default;

            if ($value === null && $removeNullFields === true)
                continue;

            if (is_bool($value) && $field->type === $field::BOOLEAN)
                $value = intval($value);

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