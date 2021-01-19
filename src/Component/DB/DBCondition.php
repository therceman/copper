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
        return self::is($field, $value, true);
    }

    public static function is($field, $value, $not = false)
    {
        if (is_string($value) === true)
            $value = "'" . str_replace("'", "''", $value) . "'";

        if ($value === null)
            $value = 'NULL';

        if (is_bool($value) === true)
            $value = intval($value);

        $cond = $field . ' IS ';

        if ($not === true)
            $cond .= 'NOT ';

        $cond .= $value;

        return $cond;
    }
}