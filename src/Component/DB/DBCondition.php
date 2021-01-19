<?php


namespace Copper\Component\DB;


class DBCondition
{
    public static function notNull($field)
    {
        return self::not($field, null);
    }

    public static function not($field, $value)
    {
        if ($value === null)
            $value = 'NULL';

        if (is_bool($value) === true)
            $value = intval($value);

        return "$field NOT $value";
    }
}