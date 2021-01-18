<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;
use Envms\FluentPDO\Exception;

abstract class ACollectionService
{
    abstract protected static function getModelClassName();

    abstract protected static function getEntityClassName();

    /**
     * @return string
     */
    private static function getTableName()
    {
        $modelClassName = static::getModelClassName();

        /** @var DBModel $model */
        $model = new $modelClassName();

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
     * @param DBHandler $db
     * @param int $id
     *
     * @return AbstractEntity|null;
     */
    public static function get(DBHandler $db, $id = 1)
    {
        try {
            $result = $db->query->from(static::getTableName(), $id)->fetch();
            $user = ($result === false) ? null : static::getEntity()::fromArray($result);
        } catch (Exception $e) {
            $user = null;
        }

        return $user;
    }

    public static function find(DBHandler $db, $filter, $limit = 50, $offset = 0)
    {
        $userList = [];

        try {
            $stmt = $db->query->from(static::getTableName())->where($filter)->limit($limit)->offset($offset);

            $result = $stmt->fetchAll();

            foreach ($result as $entry) {
                $userList[] = static::getEntity()::fromArray($entry);
            }
        } catch (Exception $e) {
            $userList = [];
        }

        return $userList;
    }

    public static function findFirst(DBHandler $db, $filter = [])
    {
        try {
            $result = $db->query->from(static::getTableName())->where($filter)->fetch();
            $user = ($result === false) ? null : static::getEntity()::fromArray($result);
        } catch (Exception $e) {
            $user = null;
        }

        return $user;
    }

    public static function delete($id)
    {
        // TODO
    }

    public static function create($data)
    {
        // TODO
    }

    public static function update($data, $id)
    {
        // TODO
    }
}