<?php


namespace Copper\Handler;


use Copper\Entity\AbstractEntity;

class CollectionHandler
{

    /**
     * @param AbstractEntity[] $collection
     * @param string $key
     *
     * @return mixed[]
     */
    public static function valueList(array $collection, string $key)
    {
        return ArrayHandler::assocValueList($collection, $key, true);
    }

    /**
     * @param AbstractEntity[] $collection
     * @param array $filter Key->Value pairs
     *
     * @return mixed[]
     */
    public static function find(array $collection, array $filter)
    {
        return ArrayHandler::assocFind($collection, $filter, true);
    }

    /**
     * @param AbstractEntity[] $collection
     * @param array $filter Key->Value pairs
     *
     * @return mixed|null
     */
    public static function findFirst(array $collection, array $filter)
    {
        $matches = self::find($collection, $filter);

        return (count($matches) > 0) ? $matches[0] : null;
    }

    public static function delete(array $collection, array $filter)
    {
        return ArrayHandler::assocDelete($collection, $filter, true);
    }
}