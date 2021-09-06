<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;
use Copper\FunctionResponse;
use Copper\FunctionResponse\DBUpdateResponse;
use Copper\FunctionResponse\DBUpdateResponseResultEntity;
use Copper\Handler\ArrayHandler;
use Copper\Handler\CollectionHandler;
use Copper\Handler\DateHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;
use Copper\Kernel;
use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Queries\Select;

abstract class DBModel
{
    const ID = 'id';

    // State Fields Support

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const REMOVED_AT = 'removed_at';
    const ENABLED = 'enabled';

    /** @var bool */
    private $stateFieldsEnabled = false;

    /** @var string */
    public $tableName = '';

    /** @var DBModelField[] */
    public $fields = [];

    /**
     * @return string
     */
    abstract function getTableName();

    /**
     * @return string
     */
    abstract function getEntityClassName();

    abstract function setFields();

    public function __construct()
    {
        $this->tableName = $this->getTableName();
        $this->setFields();
    }

    /**
     * @return AbstractEntity|false
     */
    public function getEntity()
    {
        return $this->getEntityClassName();
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function getFieldEnumValues($name)
    {
        $field = $this->getFieldByName($name);

        $length = $field->getLength();

        return (VarHandler::isArray($length)) ? $length : [];
    }

    /**
     * Returns enum values as collection
     *
     * @param string $name
     * @param string $textField
     * @param string $idField
     * @return mixed
     */
    public function getFieldEnumValuesAsCollection(string $name, $textField = 'title', $idField = DBModel::ID)
    {
        return ArrayHandler::toCollection($this->getFieldEnumValues($name), $textField, $idField);
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

    public function getFieldDefaultValue($name)
    {
        $field = $this->getFieldByName($name);

        return ($field === null) ? null : $field->getDefault();
    }

    public function getFieldNames($escaped = false)
    {
        $names = [];

        foreach ($this->fields as $field) {
            $names[] = $escaped ? '`' . DBModel::formatFieldName($field->getName()) . '`' : $field->getName();
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
        $bool = StringHandler::trim($bool);

        $true = ($bool === '1' || $bool === 1 || $bool === 'on');

        return ($true === true) ? 1 : 0;
    }

    /**
     * @param $name
     * @param bool $escape [default] = false
     *
     * @return string
     */
    public static function formatFieldName(string $name, $escape = false)
    {
        $res = preg_replace("/[^a-zA-Z0-9_]+/", "", $name);

        if ($escape === true)
            $res = '`' . $res . '`';

        return $res;
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
     * @param string $email
     *
     * @return string
     */
    public static function formatEmail(string $email)
    {
        return str_replace(['"', "'", '<', '>'], '', StringHandler::trim($email));
    }

    /**
     * Format Field Values for Update/Insert
     *
     * @param array $fieldValues
     * @param bool $removeNullFields
     * @param bool $escapeFieldNames
     *
     * @return array
     */
    public function formatFieldValues(array $fieldValues, $removeNullFields = false, $escapeFieldNames = false)
    {
        $db = Kernel::getDb();

        $formattedValues = [];

        foreach ($this->fields as $field) {
            if (array_key_exists($field->getName(), $fieldValues) === false)
                continue;

            $value = $fieldValues[$field->getName()];

            if ($db !== null && $db->config->trim_varchar && $field->typeIsVarchar())
                $value = StringHandler::trim($value);

            if ($db !== null && $db->config->trim_text && $field->typeIsText())
                $value = StringHandler::trim($value);

            if (StringHandler::trim($value) === '' && $field->typeIsString() && $field->getNull() === true)
                $value = null;

            if ($value === null && in_array($field->getType(), [$field::DATETIME, $field::DATE]) && $field->getNull() !== true)
                $value = DateHandler::dateTime();

            if ($value === null && $field->getType() === $field::YEAR && $field->getNull() !== true)
                $value = DateHandler::year();

            if ($value === null && in_array($field->getDefault(), [$field::DEFAULT_NONE, $field::DEFAULT_CURRENT_TIMESTAMP, $field::DEFAULT_NULL], true) === false && $field->getNull() !== true)
                $value = $field->getDefault();

            if ($value === null && $removeNullFields === true)
                continue;

            if (is_bool($value) && $field->getType() === $field::BOOLEAN)
                $value = intval($value);

            if ($db !== null && $db->config->boolean_not_strict && $field->getType() === $field::BOOLEAN)
                $value = VarHandler::toBooleanInt($value);

            if ($db !== null && $db->config->trim_enum && $field->typeIsEnum())
                $value = StringHandler::trim($value);

            $formattedFieldName = ($escapeFieldNames) ? '`' . $field->getName() . '`' : $field->getName();

            $formattedValues[$formattedFieldName] = $value;
        }

        return $formattedValues;
    }

    /**
     * @param AbstractEntity $entity
     * @param array|false $onlySelectedFields
     *
     * @return array
     */
    public function getFieldValuesFromEntity($entity, $onlySelectedFields = false)
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
     * @return FunctionResponse
     * @var bool $force
     *
     */
    public function doMigrate($force = false)
    {
        return DBService::migrateClassName(static::class, Kernel::getDb(), $force);
    }

    /**
     * Clear/Truncate Model Table
     *
     * @return FunctionResponse
     */
    public function doClearTable()
    {
        return DBService::tableTruncate($this->getTableName(), Kernel::getDb());
    }

    /**
     * Delete/Drop Model Table
     *
     * @return FunctionResponse
     */
    public function doDeleteTable()
    {
        return DBService::tableDelete($this->getTableName(), Kernel::getDb());
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param DBSelect $select
     *
     * @return AbstractEntity[]
     */
    public function doSelectLimit(int $limit, $offset = 0, DBSelect $select = null)
    {
        if ($select === null)
            $select = new DBSelect();

        if ($select->getLimit() === null)
            $select->setLimit($limit);

        if ($select->getOffset() === null)
            $select->setOffset($offset);

        return $this->doSelect($select);
    }

    /**
     * @param string $column
     * @param DBSelect $select
     *
     * @return AbstractEntity[]
     */
    public function doSelectUnique(string $column, DBSelect $select = null)
    {
        if ($select === null)
            $select = new DBSelect();

        if ($select->getGroup() === null)
            $select->setGroup($column);

        return $this->doSelect($select);
    }

    /**
     * @param DBSelect $select
     *
     * @return AbstractEntity|null
     */
    public function doSelectFirst(DBSelect $select)
    {
        if ($select->getLimit() === null)
            $select->setLimit(1);

        $entities = $this->doSelect($select);

        return (count($entities) > 0) ? $entities[0] : null;
    }

    /**
     * @param DBWhere $where
     * @param DBSelect|null $select
     *
     * @return AbstractEntity|null
     */
    public function doSelectFirstWhere(DBWhere $where, DBSelect $select = null)
    {
        if ($select === null)
            $select = new DBSelect();

        if ($select->getLimit() === null)
            $select->setLimit(1);

        $entities = $this->doSelectWhere($where, $select);

        return (count($entities) > 0) ? $entities[0] : null;
    }

    /**
     * @param string $field
     * @param $value
     * @param DBSelect|null $select
     *
     * @return AbstractEntity|null
     */
    public function doSelectFirstWhereIs(string $field, $value, DBSelect $select = null)
    {
        return $this->doSelectFirstWhere(DBWhere::is($field, $value), $select);
    }

    /**
     * @param int $page_item_count
     * @param int $page_num
     * @param DBSelect|null $select
     *
     * @return AbstractEntity[]
     */
    public function doSelectPaginated($page_item_count, $page_num, DBSelect $select = null)
    {
        if ($select === null)
            $select = new DBSelect();

        if ($select->getLimit() === null)
            $select->setLimit($page_item_count);

        if ($select->getOffset() === null)
            $select->setOffset(($page_num > 1) ? $page_item_count * ($page_num - 1) : 0);

        return $this->doSelect($select);
    }

    /**
     * @param DBWhere $where
     * @param DBSelect|null $select
     *
     * @return AbstractEntity[]
     */
    public function doSelectWhere(DBWhere $where, DBSelect $select = null)
    {
        if ($select === null)
            $select = new DBSelect();

        if ($select->getWhere() === null)
            $select->setWhere($where);

        return $this->doSelect($select);
    }

    /**
     * @param string $field
     * @param string|array|mixed $value
     * @param DBSelect|null $select
     *
     * @return AbstractEntity[]
     */
    public function doSelectWhereIs(string $field, $value, DBSelect $select = null)
    {
        return $this->doSelectWhere(DBWhere::is($field, $value), $select);
    }

    /**
     * @param DBColumnMod[]|null $mods
     * @param array $columns
     * @return mixed
     */
    private function processColumnMods($mods, array $columns)
    {
        foreach ($mods as $mod) {
            $column = $mod->getColumn();

            if ($column === null)
                continue;

            if (ArrayHandler::hasValue($columns, $column)) {
                $columns = ArrayHandler::replaceValue($columns, $column, $mod->getCraftedStatement());
            } else {
                $columns[] = $mod->getCraftedStatement();
            }
        }

        return $columns;
    }

    /**
     * @param DBSelect|null $select
     * @param bool $countOnly [optional] = false
     * @return Select
     * @throws Exception
     */
    private function prepareSelectStatement(DBSelect $select = null, $countOnly = false)
    {
        $db = Kernel::getDb();

        $stm = $db->query->from('`' . $this->getTableName() . '`');

        if ($select === null) {

            if ($countOnly)
                $stm = $stm->select(['COUNT(*)'], true);

            return $stm;
        }

        $columns = $select->getColumns();
        $ignoredColumns = $select->getIgnoredColumns();
        $columnMods = $select->getColumnMods();
        $limit = $select->getLimit();
        $group = $select->getGroup();
        $offset = $select->getOffset();
        $where = $select->getWhere();
        $order = $select->getOrder();

        if ($columns !== null && $ignoredColumns !== null)
            $columns = ArrayHandler::diff($columns, $ignoredColumns);

        if ($ignoredColumns !== null)
            $columns = ArrayHandler::diff($this->getFieldNames(true), $ignoredColumns);

        if ($countOnly)
            $columns = ['COUNT(*)'];

        if ($columnMods !== null)
            $columns = $this->processColumnMods($columnMods, $columns ?? $this->getFieldNames(true));

        if ($limit !== null)
            $stm = $stm->limit($limit);

        if ($group !== null)
            $stm = $stm->groupBy($group);

        if ($offset !== null)
            $stm = $stm->offset($offset);

        if ($columns !== null && count($columns) > 0)
            $stm = $stm->select($columns, true);

        if ($where !== null)
            $stm = $where->buildForStatement($stm, $columnMods);

        if ($order !== false && $order instanceof DBOrder)
            $stm->order($order);

        return $stm;
    }

    public function doGetColumns($onlyNames = false)
    {
        $db = Kernel::getDb();

        $columns = [];

        try {
            $query = $db->pdo->query('SHOW COLUMNS FROM `' . $this->getTableName() . '`');

            if ($query === false)
                return $columns;

            $columns = $query->fetchAll(\PDO::FETCH_ASSOC);

            if ($onlyNames)
                $columns = ArrayHandler::assocValueList($columns, 'Field');

            return $columns;
        } catch (Exception $e) {
            return $columns;
        }
    }

    /**
     * @param string[]|false $columns
     *
     * @return array
     */
    public function doGetTableSchema($columns = false)
    {
        $db = Kernel::getDb();

        try {
            $db_stm = $db->information_schema_query->from('TABLES')
                ->where('TABLE_SCHEMA', $db->config->dbname)
                ->where('TABLE_NAME', $this->getTableName());

            if ($columns !== false && VarHandler::isArray($columns))
                $db_stm->select($columns, true);

            $db_result = $db_stm->fetch();

            if ($db_result === false)
                $db_result = [];

        } catch (Exception $e) {
            $db_result = [];
        }

        return $db_result;
    }

    public function doGetNextId()
    {
        $result = $this->doGetTableSchema(['AUTO_INCREMENT']);

        return (int)$result['AUTO_INCREMENT'];
    }

    /**
     * @return int
     */
    public function doGetLastId()
    {
        return $this->doGetNextId() - 1;
    }

    /**
     * @param string $field
     * @param string|array|mixed $value
     * @param DBSelect|null $select
     *
     * @return int
     */
    public function doCountWhereIs(string $field, $value, DBSelect $select = null)
    {
        return $this->doCountWhere(DBWhere::is($field, $value), $select);
    }

    /**
     * @param DBWhere $where
     * @param DBSelect|null $select
     *
     * @return int
     */
    public function doCountWhere(DBWhere $where, DBSelect $select = null)
    {
        if ($select === null)
            $select = new DBSelect();

        if ($select->getWhere() === null)
            $select->setWhere($where);

        return $this->doCount($select);
    }

    /**
     * @param DBSelect $select
     *
     * @return int
     */
    public function doCount(DBSelect $select = null)
    {
        try {
            $stm = $this->prepareSelectStatement($select, true);

            $result = (int)$stm->fetchColumn();

            if ($result === false)
                $result = 0;

        } catch (Exception $e) {
            $result = 0;
        }

        return $result;
    }

    /**
     * @param DBSelect $select
     *
     * @return AbstractEntity[]
     */
    public function doSelect(DBSelect $select = null)
    {
        if ($select === null)
            $select = new DBSelect();

        if ($select->getOrder() === null)
            $select->setOrder(DBOrder::ASC(self::ID));

        $output = $select->getOutput();

        try {
            $stm = $this->prepareSelectStatement($select);

            $result = $stm->fetchAll();

            $list = [];

            if ($result === false)
                return $list;

            foreach ($result as $entry) {
                $entity = $this->getEntity()::fromArray($entry);

                if ($output !== null && $output->getMap() !== null)
                    $entity = $output->getMap()($entity);

                if ($output !== null && $output->getListOutputField() !== null && $entity->has($output->getListOutputField())) {
                    $list[] = $entity->get($output->getListOutputField());
                    continue;
                }

                if ($output !== null && $output->getDeletedFields() !== null)
                    foreach ($output->getDeletedFields() as $field) {
                        $entity->delete($field);
                    }

                if ($output !== null && $output->getFields() !== null)
                    foreach (ArrayHandler::diff($entity->getFields(), $output->getFields()) as $field) {
                        $entity->delete($field);
                    }

                if ($output !== null && $entity->has($output->getIndex()))
                    $list[$entity->get($output->getIndex())] = $entity;
                else
                    $list[] = $entity;
            }
        } catch (Exception $e) {
            $list = [];
        }

        return $list;
    }

    /**
     * Select by collection value list
     * <hr>
     * <code>
     * $productCollection = [
     *  (object) ["id" => 1, "name" => "Milk", "brand_id" => 5],
     *  (object) ["id" => 2, "name" => "Cheese", "brand_id" => 7]
     * ];
     * // Select all records from brand table where ID equals to 5 or 7
     * - Brand::getModel()->doSelectByValueList($productCollection, "brand_id")
     * </code>
     * @param AbstractEntity[] $collection
     * @param string $field
     * @param DBSelect|null $select
     *
     * @return AbstractEntity[]
     */
    public function doSelectByValueList(array $collection, string $field, DBSelect $select = null)
    {
        return $this->doSelectWhereIs(DBModel::ID, CollectionHandler::valueList($collection, $field), $select);
    }

    /**
     * Select by id list
     * <hr>
     * <code>
     * // Select all records from brand table where ID equals to 1 or 2
     * - Brand::getModel()->doSelectByIdList([1, 2])
     *
     * // Select all records using collection OR assoc array with ID keys
     * - Brand::getModel()->doSelectByIdList([["id"=>1, "name"=>"A"], ["id"=>2, "name"=>"B"]])
     * </code>
     * @param array|AbstractEntity[] $idList
     * @param DBSelect|null $select
     *
     * @return AbstractEntity[]
     */
    public function doSelectByIdList(array $idList, DBSelect $select = null)
    {
        return $this->doSelectWhereIs(DBModel::ID, $idList, $select);
    }

    /**
     * Select by id
     * <hr>
     * <code>
     * // Select first record from brand table where ID equals to 1
     * - Brand::getModel()->doSelectById(1)
     * </code>
     * @param string|int $id
     * @param DBSelect|null $select
     *
     * @return AbstractEntity|null
     */
    public function doSelectById($id, DBSelect $select = null)
    {
        return $this->doSelectFirstWhereIs(DBModel::ID, $id, $select);
    }

    /**
     * @param AbstractEntity[] $entityList
     *
     * @return FunctionResponse
     */
    public function doBulkInsert(array $entityList)
    {
        $response = new FunctionResponse();

        $db = Kernel::getDb();

        $formattedInsertDataList = [];

        foreach ($entityList as $entity) {
            $insertData = $this->getFieldValuesFromEntity($entity);
            $formattedInsertDataList[] = $this->formatFieldValues($insertData, false, true);
        }

        try {
            $stm = $db->query->insertInto('`' . $this->getTableName() . '`', $formattedInsertDataList);
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
    public function doInsert($entity)
    {
        $response = new FunctionResponse();

        $db = Kernel::getDb();

        $insertData = $this->getFieldValuesFromEntity($entity);
        $formattedInsertData = $this->formatFieldValues($insertData, true, true);

        try {
            $stm = $db->query->insertInto('`' . $this->getTableName() . '`', $formattedInsertData);
            $resultId = $stm->execute();

            if ($resultId === false)
                throw new Exception($stm->getMessage());

            $response->result($resultId);
        } catch (Exception $e) {
            $code = StringHandler::regex($e->getMessage(), '/Driver Code: (\d*)/m');
            $response->fail($e->getMessage(), $formattedInsertData, $code);
        }

        return $response;
    }

    /**
     * @param DBWhere $where
     * @param array $fields
     *
     * @return FunctionResponse
     */
    public function doUpdate(DBWhere $where, array $fields)
    {
        $response = new DBUpdateResponse();

        $db = Kernel::getDb();

        $entity = $this->getEntity()::fromArray($fields);

        $updateData = $this->getFieldValuesFromEntity($entity, array_keys($fields));
        $formattedUpdateData = $this->formatFieldValues($updateData, false, true);

        $resultRowCount = 0;

        try {
            $stm = $db->query->update('`' . $this->getTableName() . '`', $formattedUpdateData);

            if ($where !== null)
                $stm = $where->buildForStatement($stm);

            $resultRowCount = $stm->execute();

            if ($resultRowCount === false)
                throw new Exception($stm->getMessage());

            if ($resultRowCount === 0)
                throw new Exception('No record found for update or new data not provided');

            $response->result(new DBUpdateResponseResultEntity($resultRowCount, $updateData));
        } catch (Exception $e) {
            $response->fail($e->getMessage(), new DBUpdateResponseResultEntity($resultRowCount, $updateData));
        }

        return $response;
    }

    /**
     * @param int|string $id
     * @param array $fields
     *
     * @return FunctionResponse
     */
    public function doUpdateById($id, array $fields)
    {
        return $this->doUpdate(DBWhere::is(DBModel::ID, $id), $fields);
    }

    /**
     * @param DBWhere $where
     *
     * @return FunctionResponse
     */
    public function doDelete(DBWhere $where)
    {
        $response = new FunctionResponse();

        $db = Kernel::getDb();

        try {
            $stm = $db->query->delete('`' . $this->getTableName() . '`');

            if ($where !== null)
                $stm = $where->buildForStatement($stm);

            $resultRowCount = $stm->execute();

            if ($resultRowCount === false)
                throw new Exception($stm->getMessage());

            $response->result($resultRowCount);
        } catch (Exception $e) {
            $response->fail($e->getMessage());
        }

        return $response;
    }

    /**
     * @param int|string $id
     * @param string $idField
     *
     * @return FunctionResponse
     */
    public function doDeleteById($id, $idField = DBModel::ID)
    {
        return $this->doDelete(DBWhere::is($idField, $id));
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public static function hash(string $str)
    {
        return Kernel::getDb()->hash($str);
    }
}