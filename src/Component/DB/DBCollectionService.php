<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;
use Copper\FunctionResponse;
use Envms\FluentPDO\Exception;

abstract class DBCollectionService
{
    abstract protected static function getModelClassName();

    abstract protected static function getEntityClassName();

    /**
     * @return DBModel
     */
    private static function getModel()
    {
        $modelClassName = static::getModelClassName();

        return new $modelClassName();
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
     * @param bool $returnRemoved
     *
     * @return AbstractEntity[]
     */
    public static function getList(DBHandler $db, $limit = 20, $offset = 0, $order = false, $returnRemoved = false)
    {
        return self::find($db, [], $limit, $offset, $order, $returnRemoved);
    }

    /**
     * Get entry by id
     *
     * @param DBHandler $db
     * @param int $id
     * @param bool $returnRemoved
     *
     * @return AbstractEntity|null
     */
    public static function get(DBHandler $db, int $id, $returnRemoved = false)
    {
        try {
            $stm = $db->query->from(self::getTable(), $id);

            if ($returnRemoved === false && self::getModel()->hasFields([DBModel::REMOVED_AT])->isOK())
                $stm = $stm->where(DBModel::REMOVED_AT, null);

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
     * @param array|DBCondition $filter Filter: Key => Value (array) OR DBCondition::action
     * @param int $limit Limit
     * @param int $offset Offset
     * @param bool|DBOrder $order Order
     * @param bool $returnRemoved
     *
     * @return AbstractEntity[]
     */
    public static function find(DBHandler $db, $filter, $limit = 50, $offset = 0, $order = false, $returnRemoved = false)
    {
        try {
            $stm = $db->query->from(self::getTable())->limit($limit)->offset($offset);

            if ($filter instanceof DBCondition)
                $stm = $filter->buildForSelectStatement($stm);
            else
                $stm = $stm->where($filter);

            if ($returnRemoved === false && self::getModel()->hasFields([DBModel::REMOVED_AT])->isOK())
                $stm = $stm->where(DBModel::REMOVED_AT, null);

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
            $result = $db->query->from(self::getTable())->where($filter)->fetch();
            $entry = ($result === false) ? null : static::getEntity()::fromArray($result);
        } catch (Exception $e) {
            $entry = null;
        }

        return $entry;
    }

    /**
     * Remove entry by ID without undo action support
     *
     * @param DBHandler $db Database
     * @param int $id Id
     *
     * @return FunctionResponse
     */
    public static function removeWithoutUndo(DBHandler $db, int $id)
    {
        $response = new FunctionResponse();

        try {
            $stm = $db->query->delete(self::getTable(), $id);
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
     * Remove entry by ID with undo action support (if entity has EntityStateFields Trait usage)
     *
     * @param DBHandler $db Database
     * @param int $id Id
     *
     * @return FunctionResponse
     */
    public static function remove(DBHandler $db, int $id)
    {
        $hasFieldsResponse = self::getModel()->hasFields([DBModel::REMOVED_AT, DBModel::ENABLED]);

        if ($hasFieldsResponse->hasError() === true)
            return $hasFieldsResponse;

        return static::update($db, $id, [
            DBModel::REMOVED_AT => DBHandler::datetime(),
            DBModel::ENABLED => false
        ]);
    }

    /**
     * Undo Remove for entry by ID (if entity has EntityStateFields Trait usage)
     *
     * @param DBHandler $db Database
     * @param int $id Id
     *
     * @return FunctionResponse
     */
    public static function undoRemove(DBHandler $db, int $id)
    {
        $hasFieldsResponse = self::getModel()->hasFields([DBModel::REMOVED_AT]);

        if ($hasFieldsResponse->hasError() === true)
            return $hasFieldsResponse;

        return static::update($db, $id, [
            DBModel::REMOVED_AT => null
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

        $insertData = self::getModel()->getFieldValuesFromEntity($entity);
        $formattedInsertData = self::getModel()->formatFieldValues($insertData);

        $entity = $entity::fromArray($formattedInsertData);

        try {
            $stm = $db->query->insertInto(self::getTable(), $formattedInsertData);
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

        $updateData = self::getModel()->getFieldValuesFromEntity($entity, array_keys($fields));
        $formattedUpdateData = self::getModel()->formatFieldValues($updateData, false);

        try {
            $stm = $db->query->update(self::getTable(), $formattedUpdateData, $id);
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