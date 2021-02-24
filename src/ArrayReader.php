<?php


namespace Copper;


class ArrayReader
{
    /**
     * @param array $array
     *
     * @return mixed
     */
    public static function lastValue(array $array)
    {
        $val = end($array);

        reset($array);

        return $val;
    }

    /**
     * @param array $array
     *
     * @return int|string|null
     */
    public static function lastKey(array $array)
    {
        end($array);

        $key = key($array);

        reset($array);

        return $key;
    }

    /**
     * @param array|object[] $collection
     * @param string $key
     * @param bool $collectionIsObject
     *
     * @return array
     */
    public static function collectionValueList(array $collection, string $key, $collectionIsObject = true)
    {
        $list = [];

        foreach ($collection as $k => $item) {
            if ($collectionIsObject === false)
                $list[] = $item[$key];
            else
                $list[] = $item->$key;
        }

        return $list;
    }

    /**
     * @param array $collection
     * @param string $key
     * @param string|int|float|boolean $value
     * @param bool $collectionIsObject
     * @return array
     */
    public static function findInCollection(array $collection, string $key, $value, $collectionIsObject = true)
    {
        $list = [];

        foreach ($collection as $k => $item) {
            if ($collectionIsObject === false && $item[$key] !== $value)
                continue;
            else if ($collectionIsObject && $item->$key !== $value)
                continue;

            $list[] = $item;
        }

        return $list;
    }

    /**
     * Clean array of empty & null values
     *
     * @param array $array
     * @param bool $delNull - Deletes keys with value === null
     * @param bool $delEmptyStr - Deletes keys with value === ''
     * @param bool $delEmptyArray - Deletes keys with value === []
     * @param bool $isAssoc - Is associative array ? (preserve key names)
     *
     * @return array
     */
    public static function clean(array $array, bool $isAssoc = false, bool $delNull = true, bool $delEmptyStr = false, bool $delEmptyArray = false)
    {
        $cleanArray = [];

        foreach ($array as $key => $value) {
            if ($value === null && $delNull === true
                || is_string($value) && trim($value) === '' && $delEmptyStr
                || is_array($value) && count($value) === 0 && $delEmptyArray)
                continue;

            if ($isAssoc === true)
                $cleanArray[$key] = $value;
            else
                $cleanArray[] = $value;
        }

        return $cleanArray;
    }
}