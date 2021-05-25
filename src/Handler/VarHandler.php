<?php


namespace Copper\Handler;


/**
 * Class VarHandler
 * @package Copper\Handler
 */
class VarHandler
{
    /**
     * Check if value is numeric
     * <hr>
     *<code>
     * "0x539"          - false
     * "0b10100111001"  - false
     * "not numeric"    - false
     * array()          - false
     * null             - false
     * ''               - false
     * "42"             - true
     * 1337             - true
     * 0x539            - true
     * 02471            - true
     * 0b10100111001    - true
     * 1337e0           - true
     * "02471"          - true
     * "1337e0"         - true
     * 9.1              - true
     * </code>
     * @param $var
     * @return bool
     */
    public static function isNumeric($var)
    {
        return is_numeric($var);
    }

    /**
     * Check if value is float
     * <hr>
     * <code>
     * true  - false
     * 'abc' - false
     * 23    - false
     * 27.25 - true
     * 23.5  - true
     * 1e7   - true
     * </code>
     * @param mixed $var
     * @return bool
     */
    public static function isFloat($var)
    {
        return is_float($var);
    }

    /**
     * @param mixed $var
     * @return bool
     */
    public static function isObject($var)
    {
        return is_object($var);
    }

    /**
     * @param mixed $var
     * @return bool
     */
    public static function isArray($var)
    {
        return is_array($var);
    }

    /**
     * Check if value is int
     * <hr>
     * <code>
     * "23"   - false
     * 23.5   - false
     * "23.5" - false
     * null   - false
     * true   - false
     * false  - false
     * 23     - true
     * </code>
     * @param mixed $var
     * @return bool
     */
    public static function isInt($var)
    {
        return is_int($var);
    }

    /**
     * Check if value is string
     * <hr>
     * <code>
     * false  - false
     * true   - false
     * null   - false
     * 23.5   - false
     * 23     - false
     * 0      - false
     * 'abc'  - true
     * '23'   - true
     * '23.5' - true
     * ''     - true
     * ' '    - true
     * '0'    - true
     * </code>
     * @param mixed $var
     * @return bool
     */
    public static function isString($var)
    {
        return is_string($var);
    }

    /**
     * Check if value is boolean
     * <hr>
     * Strict: true
     * <code>
     * null  - false
     * 0.5   - false
     * '0'   - false
     * 0     - false
     * 1     - false
     * true  - true
     * false - true
     * </code>
     * <br>
     * Strict: false
     * <code>
     * null  - false
     * 0.5   - false
     * '0'   - true
     * 0     - true
     * 1     - true
     * true  - true
     * false - true
     * </code>
     * @param mixed $var
     * @param bool $strict
     * @return bool
     */
    public static function isBoolean($var, $strict = false)
    {
        if ($strict && is_bool($var) === false)
            return false;

        if ($var === false || $var == 0 || $var == 1 || $var === true)
            return true;

        return false;
    }

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