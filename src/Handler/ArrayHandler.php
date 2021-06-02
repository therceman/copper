<?php


namespace Copper\Handler;


use Copper\Entity\AbstractEntity;

/**
 * Class ArrayHandler
 * @package Copper\Handler
 */
class ArrayHandler
{

    /**
     * @param $array
     * @return mixed|null
     */
    public static function firstValue($array)
    {
        if (self::count($array) === 0)
            return null;

        return array_values($array)[0];
    }

    /**
     * @param array $array
     * @param mixed $search
     * @param mixed $replaceTo
     * @param bool $strict [default] = false
     *
     * @return array
     */
    public static function replaceValue(array $array, $search, $replaceTo, $strict = false)
    {
        foreach ($array as $key => $value) {
            if ($strict && $value === $search || $strict === false && VarHandler::toString($value) === VarHandler::toString($search))
                $array[$key] = $replaceTo;
        }

        return $array;
    }

    /**
     * Get intersection list of two arrays
     * <hr>
     * <code>
     * - intersect(['A','B','C'],['A','C']) // returns ['A','C']
     * - intersect(['A','B','C'],['A','D']) // returns ['A']
     * </code>
     * @param array $srcArray Source array
     * @param array $trgArray Target array
     *
     * @return array
     */
    public static function intersect(array $srcArray, array $trgArray)
    {
        return array_intersect($srcArray, $trgArray);
    }

    /**
     * Check if array has provided list of values
     * <hr>
     * <code>
     * - hasValueList(['A','B','C'],['A','C']) // returns true
     * - hasValueList(['A','B','C'],['A','D']) // returns false
     * </code>
     * @param array $array Source array
     * @param array $list List of values
     *
     * @return bool
     */
    public static function hasValueList(array $array, array $list)
    {
        return (count(self::intersect($array, $list)) === count($list));
    }

    /**
     * Get difference between two arrays.
     *
     * <hr>
     * <code>
     * - diff(['id','key','name'],['key']) // array('id','name')
     * </code>
     *
     * @param array $src Source array
     * @param array $trg Target array
     *
     * @return array Entries from source array are returned that are not found in target array
     */
    public static function diff(array $src, array $trg)
    {
        return array_diff($src, $trg);
    }

    /**
     * @param array $array
     * @param \Closure $closure
     *
     * @return mixed
     */
    public static function map(array $array, \Closure $closure)
    {
        return array_map($closure, $array);
    }

    /**
     * Transform array to collection
     *
     * @param array $array
     * @param string $textField
     * @param string $keyField
     * @return mixed
     */
    public static function toCollection(array $array, string $textField, string $keyField = 'id')
    {
        $counter = 0;
        return self::map($array, function ($text) use ($textField, $keyField, &$counter) {
            $counter++;
            return [$keyField => $counter, $textField => $text];
        });
    }

    /**
     * Create an array containing a range of elements.
     *
     * <hr>
     * <code>
     * - fromRange(1, 5)       // array(1, 2, 3, 4, 5)
     * - fromRange(0, 50, 10)  // array(0, 10, 20, 30, 40, 50)
     * - fromRange('a', 'e')   // array('a', 'b', 'c', 'd', 'e')
     * - fromRange('c', 'a')   // array('c', 'b', 'a')
     * </code>
     *
     * @param int|string|float $start start of sequence
     * @param int|string|float $end end of sequence
     * @param $step [optional] <p>Defaults to 1</p>
     *
     * @return array
     */
    public static function fromRange($start, $end, $step = 1)
    {
        return range($start, $end, $step);
    }

    /**
     * @param array $array
     * @param bool $strict [optional]
     * <p> Tell the function to consider the type of unique values </p>
     * If strict is false the function will treat '1' and 1 as the same value
     *
     * @return array
     */
    public static function uniqueValues(array $array, $strict = false)
    {
        $uniqueValues = [];

        foreach ($array as $key => $value) {
            if (ArrayHandler::hasValue($uniqueValues, $value, $strict) === false)
                $uniqueValues[] = $value;
        }

        return $uniqueValues;
    }

    /**
     * Join elements in array to string
     *
     * @param array $array
     * @param string $glue
     *
     * @return string
     */
    public static function join(array $array, $glue = ', ')
    {
        return join($glue, $array);
    }

    /**
     * Find in array
     *
     * self::find(['apple' => 1, 'banana' => 2, 'dog' => 1], function($v, $k) {
     *  return $v === 1 && $k === 'apple';
     * })
     *
     * @param array $array
     * @param \Closure $closure
     *
     * @return array
     */
    public static function find(array $array, \Closure $closure)
    {
        return array_filter($array, $closure, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Find value in array by regular expression
     *
     * @param array $array
     * @param string $regex
     * @return mixed|null
     */
    public static function findByRegex(array $array, string $regex)
    {
        $result = ArrayHandler::find($array, function ($item) use ($regex) {
            if (StringHandler::regex($item, $regex) !== false)
                return $item;
            return null;
        });

        $result = array_values($result);

        return (count($result)) ? $result : null;
    }

    /**
     * Find in array by regular expression on value
     *
     * @param array $array
     * @param string $regex
     * @return mixed|null
     */
    public static function findFirstByRegex(array $array, string $regex)
    {
        $result = self::findByRegex($array, $regex);

        return ($result !== null && count($result) > 0) ? $result[0] : null;
    }

    /**
     * Return number of items in array
     *
     * @param array $array
     * @return mixed
     */
    public static function count(array $array)
    {
        return count($array);
    }

    /**
     * Find in array and return number of found results
     *
     * @param array $array
     * @param \Closure $closure
     *
     * @return int
     */
    public static function findCount(array $array, \Closure $closure)
    {
        return self::count(self::find($array, $closure));
    }

    /**
     * @param array $array
     * @param \Closure $closure
     *
     * @return mixed|null
     */
    public static function findFirst(array $array, \Closure $closure)
    {
        $match_list = self::find($array, $closure);

        return (count($match_list) > 0) ? $match_list[0] : null;
    }

    /**
     * @param array $array
     * @param \Closure $closure
     *
     * @return mixed|null
     */
    public static function findLast(array $array, \Closure $closure)
    {
        $match_list = self::find($array, $closure);

        return (count($match_list) > 0) ? self::lastValue($match_list) : null;
    }

    /**
     * Output value based on value match in provided value list
     *
     * <hr>
     * <code>
     * - switch('red', ['black', 'red', 'white'], ['#000', '#f00', '#fff']) // #f00
     * </code>
     *
     * @param mixed $value input value
     * @param array $valueList source value list
     * @param array $outputList target value list
     *
     * @return mixed|null
     */
    public static function switch($value, array $valueList, array $outputList)
    {
        $output = null;

        foreach ($valueList as $k => $val) {
            if ($value === $val)
                $output = $outputList[$k];
        }

        return $output;
    }

    /**
     * Delete value / values from array
     * <hr>
     * <code>
     * - delete(['A','B'], 'A')           // ['B']
     * - delete(['A','B','C'], ['A','B']) // ['C']
     * </code>
     *
     * @param array $array
     * @param string|string[] $value
     *
     * @return array
     */
    public static function delete(array $array, $value)
    {
        $value_list = VarHandler::isArray($value) ? $value : [$value];

        $new_array = [];

        foreach ($array as $value) {
            if (self::hasValue($value_list, $value) === false)
                $new_array[] = $value;
        }

        return $new_array;
    }

    /**
     * Delete key / keys from array
     * <hr>
     * <code>
     * - delete(['A' => 1, 'B' => 2], 'A')                 // ['B' => 2]
     * - delete(['A' => 1, 'B' => 2, 'C' => 3], ['A','B']) // ['C' => 3]
     * </code>
     * @param array $array
     * @param string|string[] $key
     *
     * @return array
     */
    public static function deleteKey(array $array, $key)
    {
        $key_list = VarHandler::isArray($key) ? $key : [$key];

        $new_array = [];

        foreach ($array as $key => $value) {
            if (self::hasValue($key_list, $key) === false)
                $new_array[$key] = $value;
        }

        return $new_array;
    }

    /**
     * @param $array
     *
     * @return array
     */
    public static function keyList($array)
    {
        $list = [];

        foreach ($array as $key => $value) {
            $list[] = $key;
        }

        return $list;
    }

    /**
     * @param array $array
     * @param string $keyField
     *
     * @return array
     */
    public static function assocIndexList(array $array, string $keyField)
    {
        $list = [];

        foreach ($array as $k => $item) {
            if (VarHandler::isArray($item))
                $list[$item[$keyField]] = $item;
            else
                $list[$item->$keyField] = $item;
        }

        return $list;
    }

    /**
     * @param $array
     *
     * @return array
     */
    public static function valueList($array)
    {
        $list = [];

        foreach ($array as $key => $value) {
            $list[] = $value;
        }

        return $list;
    }

    /**
     * @param array|object[]|AbstractEntity[] $arrayA
     * @param array|object[]|AbstractEntity[] $arrayB
     * @param bool $reindex
     *
     * @return array|AbstractEntity[]|object[]
     */
    public static function merge_uniqueValues(array $arrayA, array $arrayB, $reindex = false)
    {
        return self::merge($arrayA, $arrayB, true, $reindex);
    }

    /**
     * @param array|object[]|AbstractEntity[] $arrayA
     * @param array|object[]|AbstractEntity[] $arrayB
     * @param bool $uniqueValues
     *
     * @return array|AbstractEntity[]|object[]
     */
    public static function merge_reindexKeys(array $arrayA, array $arrayB, $uniqueValues = false)
    {
        return self::merge($arrayA, $arrayB, $uniqueValues, true);
    }

    /**
     * @param array $arrayOfArrays
     * @param bool $uniqueValues
     * @param bool $reindexKeys
     *
     * @return array|AbstractEntity[]|object[]
     */
    public static function mergeAll(array $arrayOfArrays, $uniqueValues = false, $reindexKeys = false)
    {
        $finalArray = [];

        foreach ($arrayOfArrays as $array) {
            $finalArray = self::merge($finalArray, $array, $uniqueValues, $reindexKeys);
        }

        return $finalArray;
    }

    /**
     * @param array|object[]|AbstractEntity[] $arrayA
     * @param array|object[]|AbstractEntity[] $arrayB
     * @param bool $uniqueValues
     * @param bool $reindexKeys
     *
     * @return array|object[]|AbstractEntity[]
     */
    public static function merge(array $arrayA, array $arrayB, $uniqueValues = false, $reindexKeys = false)
    {
        $res = array_merge($arrayA, $arrayB);

        if ($uniqueValues)
            $res = array_unique($res);

        if ($reindexKeys)
            $res = array_values($res);

        return $res;
    }

    /**
     * @param array $array
     * @param mixed $value
     * @param bool $strict [optional]
     * <p> Tell the function to consider the type of provided value </p>
     * If strict is false the function will treat '1' and 1 as the same value
     * @param bool $trimNonStrictValues [optional]
     * <p> Trims values when not is strict mode </p>
     * @return bool
     */
    public static function hasValue(array $array, $value, $strict = false, $trimNonStrictValues = true)
    {
        $ok = false;

        if ($strict === false && $trimNonStrictValues)
            $value = StringHandler::trim($value);

        foreach ($array as $key => $val) {
            if ($strict && $val === $value)
                $ok = true;

            if ($strict === false) {
                if ($trimNonStrictValues)
                    $val = StringHandler::trim($val);

                if (VarHandler::toString($val) === VarHandler::toString($value))
                    $ok = true;
            }
        }

        return $ok;

        // We can't use array_search($value, $array, $strict) because of bug
        // array_search('2 2', [1, 2], false) === 1

        // Another way to solve this is to use array_flip + array_has_key
        // BUT! in this case strict check for 1 on array ['1', 2] will return true
        // $array = array_flip($array);
        // return ArrayHandler::hasKey($array, $value);
    }

    /**
     * @param array $array
     * @param mixed|null $key
     *
     * @return bool
     */
    public static function hasKey(array $array, $key)
    {
        if ($key === null)
            return false;

        return array_key_exists($key, $array);
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

    /**
     * @param array|object[]|AbstractEntity[] $array
     * @param array $filter
     *
     * @return array
     */
    public static function assocDelete(array $array, array $filter)
    {
        $newArray = [];

        foreach ($array as $key => $item) {
            if (self::assocMatch($item, $filter) === false)
                $newArray[] = $item;
        }

        return $newArray;
    }

    /**
     * @param array $array
     * @param string $keyField
     * @param string $valueField
     *
     * @return array
     */
    public static function assocKeyValueList(array $array, string $keyField, string $valueField)
    {
        $list = [];

        foreach ($array as $k => $item) {
            if (VarHandler::isArray($item))
                $list[$item[$keyField]] = $item[$valueField];
            else
                $list[$item->$keyField] = $item->$valueField;
        }

        return $list;
    }

    /**
     * @param array|object[]|AbstractEntity[] $array
     * @param string $key
     *
     * @return array
     */
    public static function assocValueList(array $array, string $key)
    {
        $list = [];

        foreach ($array as $k => $item) {
            if (VarHandler::isArray($item))
                $list[] = $item[$key];
            else
                $list[] = $item->$key;
        }

        return $list;
    }

    /**
     * @param array|object|AbstractEntity $item
     * @param array $filter
     *
     * @return bool
     */
    public static function assocMatch($item, array $filter)
    {
        $matched = true;

        $itemIsObject = true;
        if (VarHandler::isArray($item))
            $itemIsObject = false;

        foreach ($filter as $pairKey => $pairValue) {
            if ($itemIsObject === false) {
                if (VarHandler::isArray($pairValue) === false && $item[$pairKey] != $pairValue)
                    $matched = false;
                elseif (VarHandler::isArray($pairValue) && ArrayHandler::hasValue($pairValue, $item[$pairKey]) === false)
                    $matched = false;
            } else {
                if (VarHandler::isArray($pairValue) === false && $item->$pairKey != $pairValue)
                    $matched = false;
                elseif (VarHandler::isArray($pairValue) && ArrayHandler::hasValue($pairValue, $item->$pairKey) === false)
                    $matched = false;
            }
        }

        return $matched;
    }

    /**
     * @param array $array
     * @param mixed $value
     *
     * @return false|int|string
     */
    public static function findKey(array $array, $value)
    {
        return array_search($value, $array, true);
    }

    /**
     * @param array $array
     * @param array $filter
     *
     * @return int|string|null
     */
    public static function assocFindKey(array $array, array $filter)
    {
        $key = null;

        foreach ($array as $k => $item) {
            if (self::assocMatch($item, $filter))
                $key = $k;
        }

        return $key;
    }

    /**
     * @param array|object[] $array
     * @param array $filter - Key->Value pairs
     *
     * @return array
     */
    public static function assocFindStrict(array $array, array $filter)
    {
        $list = [];

        foreach ($array as $k => $item) {
            if (self::assocMatch($item, $filter))
                $list[] = $item;
        }

        return $list;
    }

    /**
     * @param array|object[] $array
     * @param array $filter - Key->Value pairs
     * @param array|object|null $default
     *
     * @return array|null
     */
    public static function assocFindStrictFirst(array $array, array $filter, $default = null)
    {
        $matches = self::assocFindStrict($array, $filter);

        return (count($matches) > 0) ? $matches[0] : $default;
    }

    /**
     * @param array $array
     * @param string $key
     * @param bool $sortASC
     *
     * @return array
     */
    public static function assocSort(array $array, string $key, $sortASC = true)
    {
        $col = array_column($array, $key);

        array_multisort($col, ($sortASC) ? SORT_ASC : SORT_DESC, $array);

        return $array;
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
                || VarHandler::isArray($value) && count($value) === 0 && $delEmptyArray)
                continue;

            if ($isAssoc === true)
                $cleanArray[$key] = $value;
            else
                $cleanArray[] = $value;
        }

        return $cleanArray;
    }

    /**
     * Decodes Objects in array to string, if object has __toString() method
     *
     * @param array $array
     */
    public static function objectsToString(array &$array)
    {
        foreach ($array as $key => $value) {
            if (is_object($value) && method_exists($value, '__toString'))
                $array[$key] = $value->__toString();
            elseif (VarHandler::isArray($value)) {
                self::objectsToString($value);
                $array[$key] = $value;
            }
        }
    }
}