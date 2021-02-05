<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;
use Copper\FunctionResponse;
use Copper\Kernel;
use DateTime;
use Envms\FluentPDO\Exception;

abstract class DBModel
{
    const ID = 'id';

    // State Fields Support

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const REMOVED_AT = 'removed_at';
    const ENABLED = 'enabled';

    /** @var bool */
    public $stateFieldsEnabled = false;

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
            if ($field->getName() === $name)
                $foundField = $field;
        }

        return $foundField;
    }

    public function getFieldNames()
    {
        $names = [];

        foreach ($this->fields as $field) {
            $names[] = $field->getName();
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
    public function addField(string $name, $type = false, $length = false)
    {
        $field = new DBModelField($name, $type, $length);

        $this->fields[] = $field;

        return $field;
    }

    public function hasStateFields()
    {
        return $this->stateFieldsEnabled;
    }

    public function addStateFields($enabledByDefault = false)
    {
        $this->addField(self::CREATED_AT, DBModelField::DATETIME)->currentTimestampByDefault();
        $this->addField(self::UPDATED_AT, DBModelField::DATETIME)->currentTimestampOnUpdate()->nullByDefault();
        $this->addField(self::REMOVED_AT, DBModelField::DATETIME)->nullByDefault();
        $this->addField(self::ENABLED, DBModelField::BOOLEAN)->default($enabledByDefault);

        $this->stateFieldsEnabled = true;
    }

    /**
     * @param string $bool
     *
     * @return integer
     */
    public static function formatBoolean(string $bool)
    {
        $bool = trim($bool);

        $true = ($bool === '1' || $bool === 1 || $bool === 'on');

        return ($true === true) ? 1 : 0;
    }

    /**
     * @param $date
     * @param $fromFormat
     *
     * @return string
     */
    public static function formatDate($date, $fromFormat)
    {
        return DateTime::createFromFormat($fromFormat, $date)->format('Y-m-d H:i:s');
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
        return floatval(preg_replace('/[^.0-9\-]/', '', $number));
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
            if (array_key_exists($field->getName(), $fieldValues) === false)
                continue;

            $value = $fieldValues[$field->getName()];

            if (trim($value) === '' && $field->typeIsString() && $field->getNull() === true)
                $value = null;

            if ($value === null && in_array($field->getType(), [$field::DATETIME, $field::DATE]) && $field->getNull() !== true)
                $value = DBHandler::datetime();

            if ($value === null && $field->getType() === $field::YEAR && $field->getNull() !== true)
                $value = DBHandler::year();

            if ($value === null && in_array($field->getDefault(), [$field::DEFAULT_NONE, $field::DEFAULT_CURRENT_TIMESTAMP, $field::DEFAULT_NULL], true) === false && $field->getNull() !== true)
                $value = $field->getDefault();

            if ($value === null && $removeNullFields === true)
                continue;

            if (is_bool($value) && $field->getType() === $field::BOOLEAN)
                $value = intval($value);

            $formattedValues['`' . $field->getName() . '`'] = $value;
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
            if (array_key_exists($field->getName(), $entityFields) === false)
                continue;

            if ($onlySelectedFields !== false && array_search($field->getName(), $onlySelectedFields) === false)
                continue;

            $fieldValues[$field->getName()] = $entityFields[$field->getName()];
        }

        return $fieldValues;
    }

    /**
     * Migrate Model Table
     *
     * @param boolean $force
     *
     * @return FunctionResponse
     */
    public function doMigrate($force = false)
    {
        return DBService::migrateClassName(self::class, Kernel::getDb(), $force);
    }

    /**
     * Truncate Model Table
     *
     * @return FunctionResponse
     */
    public function doTruncate()
    {
        return DBService::tableTruncate($this->getTableName(), Kernel::getDb());
    }

    /**
     * @param integer|DBCondition $keyOrCondition
     */
    public function doSelect($keyOrCondition)
    {

    }

    /**
     * @param AbstractEntity[] $entityList
     *
     * @return FunctionResponse
     */
    public function doBulkInsert($entityList)
    {
        $response = new FunctionResponse();

        $db = Kernel::getDb();

        $formattedInsertDataList = [];

        foreach ($entityList as $entity) {
            $insertData = $this->getFieldValuesFromEntity($entity);
            $formattedInsertDataList[] = $this->formatFieldValues($insertData, false);
        }

        try {
            $stm = $db->query->insertInto($this->getTableName(), $formattedInsertDataList);
            $result = $stm->execute();

            if ($result === false)
                throw new Exception($stm->getMessage());

            $response->result($stm->getTotalTime());
        } catch (Exception $e) {
            $response->fail($e->getMessage());
        }

        return $response;
    }

    /**
     * @param AbstractEntity $entity
     *
     * @return FunctionResponse
     */
    public function doInsert(AbstractEntity $entity)
    {
        $response = new FunctionResponse();

        $db = Kernel::getDb();

        $insertData = $this->getFieldValuesFromEntity($entity);
        $formattedInsertData = $this->formatFieldValues($insertData);

        $entity = $entity::fromArray($formattedInsertData);

        try {
            $stm = $db->query->insertInto($this->getTableName(), $formattedInsertData);
            $resultId = $stm->execute();

            if ($resultId === false)
                throw new Exception($stm->getMessage());

            $entity->id = $resultId;
            $response->result($entity->toArray());
        } catch (Exception $e) {
            $response->fail($e->getMessage(), $formattedInsertData);
        }

        return $response;
    }

    /**
     * @param integer|DBCondition $keyOrCondition
     * @param array $fields
     */
    public function doUpdate($keyOrCondition, array $fields)
    {

    }

    /**
     * @param integer|DBCondition $keyOrCondition
     */
    public function doDelete($keyOrCondition)
    {

    }

}