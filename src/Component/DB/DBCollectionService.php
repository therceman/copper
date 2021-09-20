<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;
use Copper\FunctionResponse;
use Copper\Handler\DateHandler;
use Envms\FluentPDO\Exception;

abstract class DBCollectionService
{
    /** @var array */
    private static $models = [];

    abstract protected static function getModelClassName();

    abstract protected static function getEntityClassName();

    /**
     * @return DBModel
     */
    public static function getModel()
    {
        $modelClassName = static::getModelClassName();

        if (array_key_exists(static::class, self::$models) === false)
            self::$models[static::class] = new $modelClassName();

        return self::$models[static::class];
    }

    /**
     * @return string
     */
    private static function getTable()
    {
        $model = self::getModel();

        return $model->tableName;
    }

    /**
     * @return AbstractEntity
     */
    private static function getEntity()
    {
        $entityClassName = static::getEntityClassName();

        return new $entityClassName();
    }

    /**
     * @param DBHandler $db
     * @param int $id
     * @param bool $enabled
     *
     * @return FunctionResponse
     */
    private static function updateEnabledStatus(DBHandler $db, int $id, $enabled = true)
    {
        $hasFieldsResponse = self::getModel()->hasFields([DBModel::ENABLED]);

        if ($hasFieldsResponse->hasError() === true)
            return $hasFieldsResponse;

        return static::update($db, $id, [
            DBModel::ENABLED => $enabled
        ]);
    }

    /**
     * Get entry list
     *
     * @param DBHandler $db
     * @param int $limit
     * @param int $offset
     * @param bool|DBOrder $order
     * @param bool $returnArchived
     *
     * @return AbstractEntity[]
     */
    public static function getList(DBHandler $db, $limit = 20, $offset = 0, $order = false, $returnArchived = false)
    {
        return self::find($db, [], $limit, $offset, $order, $returnArchived);
    }

    /**
     * Get entry by id
     *
     * @param DBHandler $db
     * @param int $id
     * @param bool $returnArchived
     *
     * @return AbstractEntity|null
     */
    public static function get(DBHandler $db, int $id, $returnArchived = false)
    {
        try {
            $stm = $db->query->from('`'.self::getTable().'`', $id);

            if ($returnArchived === false && self::getModel()->hasFields([DBModel::ARCHIVED_AT])->isOK())
                $stm = $stm->where(DBModel::ARCHIVED_AT, null);

            $result = $stm->fetch();

            $entry = ($result === false) ? null : static::getEntity()::fromArray($result);
        } catch (Exception $e) {
            $entry = null;
        }

        return $entry;
    }

    /**
     * Find entries by filter.
     * Usage: find($db, ['enabled' => true], 20, 20) - Find all enabled users and show second page of results.
     *
     * @param DBHandler $db Database
     * @param array|DBWhere $filter Filter: Key => Value (array) OR DBWhere::action
     * @param int $limit Limit
     * @param int $offset Offset
     * @param bool|DBOrder $order Order
     * @param bool $returnArchived
     *
     * @return AbstractEntity[]
     */
    public static function find(DBHandler $db, $filter, $limit = 50, $offset = 0, $order = false, $returnArchived = false)
    {
        try {
            $stm = $db->query->from('`'.self::getTable().'`')->limit($limit)->offset($offset);

            if ($filter instanceof DBWhere)
                $stm = $filter->buildForStatement($stm);
            else
                $stm = $stm->where($filter);

            if ($returnArchived === false && self::getModel()->hasFields([DBModel::ARCHIVED_AT])->isOK())
                $stm = $stm->where(DBModel::ARCHIVED_AT, null);

            if ($order !== false && $order instanceof DBOrder)
                $stm->order($order);

            $result = $stm->fetchAll();

            $list = [];

            if ($result === false)
                return $list;

            foreach ($result as $entry) {
                $list[] = static::getEntity()::fromArray($entry);
            }
        } catch (Exception $e) {
            $list = [];
        }

        return $list;
    }

    /**
     * Find first entry by filter.
     * Usage: find($db, ['email' => 'john@copper.com']) - Find first user with provided email and returns it (if found)
     *
     * @param DBHandler $db Database
     * @param array $filter Filter: Key => Value array
     *
     * @return AbstractEntity|null
     */
    public static function findFirst(DBHandler $db, array $filter)
    {
        try {
            $result = $db->query->from('`'.self::getTable().'`')->where($filter)->fetch();
            $entry = ($result === false) ? null : static::getEntity()::fromArray($result);
        } catch (Exception $e) {
            $entry = null;
        }

        return $entry;
    }

    /**
     * Delete entry by ID (remove without undo action support)
     *
     * @param DBHandler $db Database
     * @param int $id Id
     *
     * @return FunctionResponse
     */
    public static function delete(DBHandler $db, int $id)
    {
        $response = new FunctionResponse();

        try {
            $stm = $db->query->delete('`'.self::getTable().'`', $id);
            $resultRowCount = $stm->execute();

            if ($resultRowCount === false)
                throw new Exception($stm->getMessage());

            if ($resultRowCount === 0)
                $response->fail("Entry with ID '$id' not found.");
            else
                $response->result($resultRowCount);
        } catch (Exception $e) {
            $response->fail($e->getMessage());
        }

        return $response;
    }

    /**
     * Archive entry by ID with undo action support (if entity has EntityStateFields Trait usage)
     *
     * @param DBHandler $db Database
     * @param int $id Id
     *
     * @return FunctionResponse
     */
    public static function archive(DBHandler $db, int $id)
    {
        $hasFieldsResponse = self::getModel()->hasFields([DBModel::ARCHIVED_AT, DBModel::ENABLED]);

        if ($hasFieldsResponse->hasError() === true)
            return $hasFieldsResponse;

        return static::update($db, $id, [
            DBModel::ARCHIVED_AT => DateHandler::dateTime(),
            DBModel::ENABLED => false
        ]);
    }

    /**
     * Undo Archive for entry by ID (if entity has EntityStateFields Trait usage)
     *
     * @param DBHandler $db Database
     * @param int $id Id
     *
     * @return FunctionResponse
     */
    public static function undoArchive(DBHandler $db, int $id)
    {
        $hasFieldsResponse = self::getModel()->hasFields([DBModel::ARCHIVED_AT]);

        if ($hasFieldsResponse->hasError() === true)
            return $hasFieldsResponse;

        return static::update($db, $id, [
            DBModel::ARCHIVED_AT => null
        ]);
    }

    /**
     * Enable entry (if entity has EntityStateFields Trait usage)
     *
     * @param DBHandler $db
     * @param int $id
     *
     * @return FunctionResponse
     */
    public static function enable(DBHandler $db, int $id)
    {
        return static::updateEnabledStatus($db, $id, true);
    }

    /**
     * Disable entry (if entity has EntityStateFields Trait usage)
     *
     * @param DBHandler $db
     * @param int $id
     *
     * @return FunctionResponse
     */
    public static function disable(DBHandler $db, int $id)
    {
        return static::updateEnabledStatus($db, $id, false);
    }

    /**
     * Create new entry using entity
     *
     * @param DBHandler $db DB
     * @param AbstractEntity $entity Entity
     *
     * @return FunctionResponse
     */
    public static function create(DBHandler $db, AbstractEntity $entity)
    {
        $response = new FunctionResponse();

        // prepareDataForInsert($entity)
        // TODO put the whole create/update/etc. function to the Model
        $insertData = self::getModel()->getFieldValuesFromEntity($entity);
        $formattedInsertData = self::getModel()->formatFieldValues($insertData,true,true);

        try {
            $stm = $db->query->insertInto('`'.self::getTable().'`', $formattedInsertData);
            $resultId = $stm->execute();

            if ($resultId === false)
                throw new Exception($stm->getMessage());

            $response->result($resultId);
        } catch (Exception $e) {
            $response->fail($e->getMessage(), $formattedInsertData);
        }

        return $response;
    }

    /**
     * Update specific fields for entry
     *
     * @param DBHandler $db DB
     * @param int $id Id
     * @param array $fields Fields: Key => Value array
     *
     * @return FunctionResponse
     */
    public static function update(DBHandler $db, int $id, array $fields)
    {
        $response = new FunctionResponse();

        $entity = self::getEntity()::fromArray($fields);

        // prepareDataForUpdate($fields, $entity?);
        $updateData = self::getModel()->getFieldValuesFromEntity($entity, array_keys($fields));
        $formattedUpdateData = self::getModel()->formatFieldValues($updateData, false, true);

        try {
            $stm = $db->query->update('`'.self::getTable().'`', $formattedUpdateData, $id);
            $resultRowCount = $stm->execute();

            if ($resultRowCount === false)
                throw new Exception($stm->getMessage());

            $response->result($resultRowCount);
        } catch (Exception $e) {
            $response->fail($e->getMessage(), $formattedUpdateData);
        }

        return $response;
    }
}