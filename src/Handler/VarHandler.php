<?php


namespace Copper\Handler;


/**
 * Class VarHandler
 * @package Copper\Handler
 */
class VarHandler
{

    /**
     * Get type of variable
     * <hr>
     * <code>
     * "boolean"
     * "integer"
     * "float"
     * "string"
     * "array"
     * "object"
     * "resource"
     * "resource (closed)" as of PHP 7.2.0
     * "NULL"
     * "unknown type"
     * </code>
     * @param mixed $var
     * @return string
     */
    public static function getType($var)
    {
        // Fix for: (for historical reasons "double" is returned in case of a float, and not simply "float")
        if (self::isFloat($var))
            return "float";

        return gettype($var);
    }

    /**
     * Transform variable to integer
     * echo intval(42);                      // 42
     * echo intval(4.2);                     // 4
     * echo intval('42');                    // 42
     * echo intval('+42');                   // 42
     * echo intval('-42');                   // -42
     * echo intval(042);                     // 34
     * echo intval('042');                   // 42
     * echo intval(1e10);                    // 1410065408
     * echo intval('1e10');                  // 1
     * echo intval(0x1A);                    // 26
     * echo intval(array());                 // 0
     * echo intval(array('foo', 'bar'));     // 0
     * echo intval($object);                 // 0
     * echo intval(false);                   // 0
     * echo intval(true);                    // 1
     * echo intval(null);                    // 0
     * @param $var
     * @return int
     */
    public static function toInt($var)
    {
        if ($var === true)
            return 1;

        if (VarHandler::isArray($var) || VarHandler::isObject($var) || VarHandler::isNull($var) || $var === false)
            return 0;

        return intval($var);
    }

    public static function toFloat($var)
    {
        if ($var === true)
            return 1;

        if (VarHandler::isArray($var) || VarHandler::isObject($var) || VarHandler::isNull($var) || $var === false)
            return 0;

        return floatval($var);
    }

    public static function isJSON($var)
    {
        if (is_string($var) === false)
            return false;

        $result = json_decode($var);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE)
            return false;

        return true;
    }

    public static function isEmpty($var)
    {
        if (self::isArray($var))
            return (count($var) === 0);

        if (self::isString($var))
            return (StringHandler::trim($var) === '');

        if ($var === null)
            return true;

        return false;
    }

    /**
     * @param mixed $var
     * @param bool $allowSpaces
     * @param string|null $extraAllowedCharacters
     * @return bool
     */
    public static function isAlphaNumeric($var, $allowSpaces = true, $extraAllowedCharacters = null)
    {
        $spaceDelimiter = ($allowSpaces) ? ' ' : '';

        if (self::isBoolean($var) || self::isArray($var) || self::isObject($var) || self::isNull($var))
            return false;

        $extraCharacters = '';
        if ($extraAllowedCharacters !== null)
            $extraCharacters = $extraAllowedCharacters;

        $var = strval($var);

        return (StringHandler::regex($var, '/(^[\pL\pN' . $spaceDelimiter . $extraCharacters . ']+$)/u') !== false);
    }

    /**
     * @param mixed $var
     * @param bool $allowSpaces
     * @param string|null $extraAllowedCharacters
     * @return bool
     */
    public static function isAlpha($var, $allowSpaces = true, $extraAllowedCharacters = null)
    {
        $spaceDelimiter = ($allowSpaces) ? ' ' : '';

        if (self::isString($var) === false)
            return false;

        $extraCharacters = '';
        if ($extraAllowedCharacters !== null)
            $extraCharacters = $extraAllowedCharacters;

        return (StringHandler::regex($var, '/(^[\pL' . $spaceDelimiter . $extraCharacters . ']+$)/u') !== false);
    }

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
     * " 9.1 "          - true
     * </code>
     * @param $var
     * @return bool
     */
    public static function isNumeric($var, $strict = false)
    {
        if (self::isString($var)) {
            $var = StringHandler::trim($var);

            if (StringHandler::has(StringHandler::toLowerCase($var), 'e') && $strict === false)
                return false;
        }

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
     * @param bool $strict
     * @return bool
     */
    public static function isFloat($var, $strict = true)
    {
        if ($var === null || $var === true || $var === false || self::isArray($var) || self::isObject($var))
            return false;

        if ($strict === false && self::isNumeric($var))
            $var = floatval($var);

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
    public static function isNull($var)
    {
        return $var === null;
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
     * @param bool $strict
     * @return bool
     */
    public static function isInt($var, $strict = true)
    {
        if ($strict === false)
            return (StringHandler::regex(StringHandler::trim(self::toString($var)), '(^[\-]?[\pN]+$)') !== false);

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

        if ($var === 0 || $var === 1 || $var === '0' || $var === '1')
            return true;

        if ($var === false || $var === true || $var === 'false' || $var === 'true')
            return true;

        return false;
    }

    /**
     * @param mixed $var
     * @return false|string
     */
    public static function toJSON($var)
    {
        return json_encode($var);
    }

    /**
     * @param mixed $var
     * @return false|string
     */
    public static function toString($var)
    {
        if (self::isNull($var))
            return 'null';

        if (self::isBoolean($var, true))
            return self::toBooleanString($var);

        if (self::isArray($var) || self::isObject($var))
            return json_encode($var);

        return strval($var);
    }

    /**
     * @param $var
     * @return bool
     */
    public static function toBoolean($var)
    {
        if (self::isString($var))
            $var = StringHandler::trim($var);

        if ($var === true || $var === 1 || $var === '1' || $var === 'true')
            return true;

        return false;
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