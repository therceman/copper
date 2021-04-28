<?php


namespace Copper\Handler;


use Copper\Component\DB\DBModel;
use Copper\Entity\AbstractEntity;

class CollectionHandler
{

    /**
     * @param AbstractEntity[]|object[] $collection
     * @param string $key
     *
     * @return mixed[]
     */
    public static function valueList(array $collection, string $key)
    {
        return ArrayHandler::assocValueList($collection, $key);
    }

    /**
     * @param AbstractEntity[]|object[] $collection
     * @param string $keyField
     * @param string $valueField
     *
     * @return mixed[]
     */
    public static function keyValueList(array $collection, string $keyField, string $valueField)
    {
        return ArrayHandler::assocKeyValueList($collection, $keyField, $valueField);
    }

    /**
     * @param AbstractEntity[]|object[] $collection
     * @param string $keyField
     *
     * @return mixed[]
     */
    public static function indexList(array $collection, string $keyField = DBModel::ID)
    {
        return ArrayHandler::assocIndexList($collection, $keyField);
    }

    /**
     * @param array $collection
     * @param \Closure $closure
     *
     * @return mixed[]
     */
    public static function find(array $collection, \Closure $closure)
    {
        return ArrayHandler::find($collection, $closure);
    }

    /**
     * @param AbstractEntity[]|object[] $collection
     * @param array $filter Key->Value pairs
     *
     * @return mixed[]
     */
    public static function findStrict(array $collection, array $filter)
    {
        return ArrayHandler::assocFindStrict($collection, $filter);
    }

    /**
     * @param AbstractEntity[]|object[] $collection
     * @param array $filter Key->Value pairs
     * @param AbstractEntity|object|null $default
     *
     * @return AbstractEntity|object|null
     */
    public static function findStrictFirst(array $collection, array $filter, $default = null)
    {
        $matches = self::findStrict($collection, $filter);

        return (count($matches) > 0) ? $matches[0] : $default;
    }

    /**
     * @param array $collection
     * @param string $key
     * @param mixed $value
     * @param AbstractEntity|object|null $default
     *
     * @return AbstractEntity|object|null
     */
    public static function findFirstBy(array $collection, string $key, $value, $default = null)
    {
        return self::findStrictFirst($collection, [$key => $value], $default);
    }

    /**
     * @param AbstractEntity[]|object[] $collection
     * @param \Closure $closure
     *
     * @return AbstractEntity[]|object[]
     */
    public static function map(array $collection, \Closure $closure)
    {
        return ArrayHandler::map($collection, $closure);
    }

    /**
     * @param array $collection
     * @param int|string $id
     * @param AbstractEntity|object|null $default
     *
     * @return AbstractEntity|object|null
     */
    public static function findFirstById(array $collection, $id, $default = null)
    {
        return self::findFirstBy($collection, DBModel::ID, $id, $default);
    }

    /**
     * @param AbstractEntity[]|object[] $collection
     * @param array $filter
     *
     * @return AbstractEntity[]|object[]
     */
    public static function delete(array $collection, array $filter)
    {
        return ArrayHandler::assocDelete($collection, $filter);
    }
}