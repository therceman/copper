<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;
use Copper\Entity\FunctionResponse;
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

    public static function getList(DBHandler $db, $limit = 20, $offset = 0, $returnRemoved = false)
    {


    }

    /**
     * Get entry by id
     *
     * @param DBHandler $db
     * @param int $id
     *
     * @return AbstractEntity|null
     */
    public static function get(DBHandler $db, int $id)
    {
        try {
            $result = $db->query->from(self::getTable(), $id)->fetch();
            $user = ($result === false) ? null : static::getEntity()::fromArray($result);
        } catch (Exception $e) {
            $user = null;
        }

        return $user;
    }

    /**
     * Find entries by filter.
     * Usage: find($db, ['enabled' => true], 20, 20) - Find all enabled users and show second page of results.
     *
     * @param DBHandler $db Database
     * @param array $filter Filter: Key => Value array
     * @param int $limit Limit
     * @param int $offset Offset
     *
     * @return AbstractEntity[]
     */
    public static function find(DBHandler $db, array $filter, $limit = 50, $offset = 0)
    {
        try {
            $result = $db->query->from(self::getTable())->where($filter)->limit($limit)->offset($offset)->fetchAll();

            $userList = [];
            foreach ($result as $entry) {
                $userList[] = static::getEntity()::fromArray($entry);
            }
        } catch (Exception $e) {
            $userList = [];
        }

        return $userList;
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
            $user = ($result === false) ? null : static::getEntity()::fromArray($result);
        } catch (Exception $e) {
            $user = null;
        }

        return $user;
    }

    /**
     * Delete entry by ID
     *
     * @param DBHandler $db Database
     * @param int $id Id
     * @param bool $force TODO - $db->query->delete vs UPDATE removed_at timestamp
     *
     * @return bool
     */
    public static function delete(DBHandler $db, int $id, $force = false)
    {
        // TODO set removed_at timestamp
        // TODO set enabled = false

        try {
            $result = $db->query->delete(self::getTable(), $id)->execute();
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    public static function undo_delete(DBHandler $db, int $id) {
        // TODO - remove removed_at timestamp

        // We don't set enabled = true
        // IF admin removed bad user and accidentally clicked undo, the bad user will be active instantly - VERY BAD


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

        foreach ($entity->toArray() as $key => $value) {
            $insertData[$key] = str_replace("'", "", $value);

            if (array_key_exists($key, $insertData) && $value === null && $key !== 'created_at')
                unset($insertData[$key]);
        }


        $insertData['created_at'] = date('Y-m-d H:i:s');

        var_dump($insertData);

        try {
            $id = $db->query->insertInto(self::getTable(), $insertData)->execute();
            $entity->id = $id;
            $response->okOrFail(($id !== false), $entity);
        } catch (Exception $e) {
            $response->fail($e->getMessage(), $insertData);
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

        try {
            $status = $db->query->update(self::getTable(), $updateData, $id)->execute();
            $response->okOrFail($status, $updateData);
        } catch (Exception $e) {
            $response->fail($e->getMessage(), $updateData);
        }

        return $response;
    }
}