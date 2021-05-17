<?php


namespace Copper\Handler;


/**
 * Class VarHandler
 * @package Copper\Handler
 */
class VarHandler
{
    /**
     * @param $var
     * @return bool
     */
    public static function toBoolean($var)
    {
        if ($var === false || $var === null || $var === 0 || $var === '0' || $var === '')
            return false;

        return true;
    }

    /**
     * @param $var
     * @return string
     */
    public static function toBooleanString($var)
    {
        return (self::toBoolean($var)) ? 'true' : 'false';
    }

    /**
     * @param $var
     * @return int
     */
    public static function toBooleanInt($var)
    {
        return (self::toBoolean($var)) ? 1 : 0;
    }

}