<?php


namespace Copper\Handler;


class ArrayHandler
{
    public static function switch($value, $valueList, $outputList)
    {
        $output = null;

        foreach ($valueList as $k => $val) {
            if ($value === $val)
                $output = $outputList[$k];
        }

        return $output;
    }

    public static function merge($arrayA, $arrayB, $uniqueValues = true, $reindex = true)
    {
        $res = array_merge($arrayA, $arrayB);

        if ($uniqueValues)
            $res = array_unique($res);

        if ($reindex)
            $res = array_values($res);

        return $res;
    }

    /**
     * @param array $array
     * @param mixed $value
     * @param bool $strict
     *
     * @return mixed|null
     */
    public static function hasValue(array $array, $value, $strict = true)
    {
        $key = array_search($value, $array, $strict);

        return ($key !== false);
    }

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

    public static function assocDelete(array $array, array $filter, $arrayOfObjects = false)
    {
        $newArray = [];

        foreach ($array as $key => $item) {
            if (self::assocMatch($item, $filter, $arrayOfObjects) === false)
                $newArray[] = $item;
        }

        return $newArray;
    }

    /**
     * @param array|object[] $array
     * @param string $key
     * @param bool $arrayOfObjects
     *
     * @return array
     */
    public static function assocValueList(array $array, string $key, $arrayOfObjects = true)
    {
        $list = [];

        foreach ($array as $k => $item) {
            if ($arrayOfObjects === false)
                $list[] = $item[$key];
            else
                $list[] = $item->$key;
        }

        return $list;
    }

    /**
     * @param array $item
     * @param array $filter
     * @param bool $itemIsObject
     *
     * @return bool
     */
    public static function assocMatch(array $item, array $filter, $itemIsObject = false)
    {
        $matched = true;

        foreach ($filter as $pairKey => $pairValue) {
            if ($itemIsObject === false) {
                if (is_array($pairValue) === false && $item[$pairKey] != $pairValue)
                    $matched = false;
                elseif (is_array($pairValue) && ArrayHandler::hasValue($pairValue, $item[$pairKey]) === false)
                    $matched = false;
            } elseif ($itemIsObject) {
                if (is_array($pairValue) === false && $item->$pairKey != $pairValue)
                    $matched = false;
                elseif (is_array($pairValue) && ArrayHandler::hasValue($pairValue, $item->$pairKey) === false)
                    $matched = false;
            }
        }

        if ($matched)
            return true;
    }

    /**
     * @param array|object[] $array
     * @param array $filter - Key->Value pairs
     * @param bool $arrayOfObjects
     *
     * @return array
     */
    public static function assocFind(array $array, array $filter, $arrayOfObjects = false)
    {
        $list = [];

        foreach ($array as $k => $item) {
            if (self::assocMatch($item, $filter, $arrayOfObjects))
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