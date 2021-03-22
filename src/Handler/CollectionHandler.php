<?php


namespace Copper\Handler;


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
     * @param array $filter Key->Value pairs
     *
     * @return mixed[]
     */
    public static function find(array $collection, array $filter)
    {
        return ArrayHandler::assocFind($collection, $filter);
    }

    /**
     * @param AbstractEntity[]|object[] $collection
     * @param array $filter Key->Value pairs
     *
     * @return AbstractEntity|object|null
     */
    public static function findFirst(array $collection, array $filter)
    {
        $matches = self::find($collection, $filter);

        return (count($matches) > 0) ? $matches[0] : null;
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