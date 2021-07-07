<?php


namespace Copper\Handler;


use Copper\Component\DB\DBModel;
use Copper\Kernel;
use Copper\Transliterator;

/**
 * Class StringHandler
 * @package Copper\Handler
 */
class StringHandler
{
    /**
     * Removes white spaces around keys and values in JSON string
     *
     * @param string $json
     * @return string
     */
    public static function trimJSON(string $json)
    {
        foreach (StringHandler::regexAll($json, '/"(\s{0,}\p{L}*\s{0,}\p{L}.\s{0,})"/mu') as $group) {
            $json = StringHandler::replace($json, $group[1], StringHandler::trim($group[1]));
        }

        return $json;
    }

    /**
     * Explode/Split string by delimiter
     *
     * @param string $str
     * @param string $delimiter
     *
     * @return string[]
     */
    public static function split(string $str, string $delimiter = ',')
    {
        if (strlen(self::trim($str)) === 0)
            return [];

        $res = explode($delimiter, $str);

        return ($res === false) ? [] : $res;
    }

    /**
     * @param string $str
     * @param array $args
     *
     * @return string
     */
    public static function sprintf(string $str, array $args)
    {
        return sprintf($str, ...$args);
    }

    /**
     * @param mixed $var
     * @param bool $flatten
     *
     * @return false|string
     */
    public static function dump($var, $flatten = false)
    {
        ob_start();
        var_dump($var);
        $out = ob_get_clean();

        if ($flatten) {
            $n = '%{[\n]}%';
            $out = StringHandler::replace($out, "\n", $n);
            $out = self::replace($out, ['{' . $n . '  ', $n . '}', '}' . $n, '=>' . $n . ' ', $n . '  '],
                ['{', '}', '}', '=>', ', ']);
            $out = self::replace($out, $n, '');
        }

        return $out;
    }

    /**
     * Find and replace text in string
     * <hr>
     * <code>
     * - replace('A B', 'B', 123)                   // returns "A 123"
     * - replace('A B C', ['A', 'B'], 'X')          // returns "X X C"
     * - replace('A B C', ['A', 'B'], ['A1', 'B2']) // returns "A1 B2 C"
     * </code>
     *
     * @param string $str
     * @param int|int[]|float|float[]|string|string[] $search
     * @param int|int[]|float|float[]|string|string[] $replaceTo
     *
     * @return string
     */
    public static function replace(string $str, $search, $replaceTo)
    {
        return str_replace($search, $replaceTo, $str);
    }

    /**
     * Find and replace text in string recursively (until no match)
     * <hr>
     * <code>
     * - replaceRecursively('AABAB','AB','B') // returns BB
     * # compared to replace('AABAB','AB','B') that returns ABB
     * </code>
     * @param string $str
     * @param string|int $search
     * @param string|int $replaceTo
     *
     * @return string
     */
    public static function replaceRecursively(string $str, $search, $replaceTo)
    {
        $res = self::replace($str, $search, $replaceTo);

        if (self::has($res, $search))
            $res = self::replaceRecursively($res, $search, $replaceTo);

        return $res;
    }

    public static function random($len = 5)
    {
        $hash = DBModel::hash(NumberHandler::random(0, 1000 * $len));

        return StringHandler::substr($hash, 0, $len);
    }

    /**
     * Cut a string / return part of a string
     *
     * <hr>
     * <code>
     * - substr("abcdef", -1);     // returns "f"
     * - substr("abcdef", -2);     // returns "ef"
     * - substr("abcdef", -3, 1);  // returns "d"
     * - substr("abcdef", -10, 1); // returns ""
     * </code>
     *
     * @param string $str
     * @param int $start
     * @param int|null $length
     *
     * @return false|string
     */
    public static function substr(string $str, int $start, $length = null)
    {
        return ($length === null) ? substr($str, $start) : substr($str, $start, $length);
    }

    public static function repeat($str, $num_of_times)
    {
        return str_repeat($str, $num_of_times);
    }

    /**
     * @param string $str
     * @param int|int[]|float|float[]|string|string[] $search
     *
     * @return string
     */
    public static function delete(string $str, $search)
    {
        return self::replace($str, $search, '');
    }

    /**
     * Check if string has/contains text
     *
     * @param $str
     * @param $text
     *
     * @return bool
     */
    public static function has($str, $text)
    {
        return (strpos($str, $text) !== false);
    }

    public static function trim($str)
    {
        if ($str === null)
            return '';

        if (VarHandler::isString($str) === false)
            return $str;

        $str = trim($str);

        //handle unicode spaces
        $str = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $str);

        return $str;
    }

    /**
     * Check if string is not empty. The string is trimmed by default before check.
     * @param string|null $str
     * @param bool $trim
     * @return bool
     */
    public static function isNotEmpty(?string $str, $trim = true)
    {
        return (self::isEmpty($str, $trim) === false);
    }

    /**
     * Check if string is empty. The string is trimmed by default before check.
     * @param string|null $str
     * @param bool $trim
     * @return bool
     */
    public static function isEmpty(?string $str, $trim = true)
    {
        if ($str === null)
            return true;

        if ($trim)
            $str = self::trim($str);

        return $str === '';
    }

    /**
     * @param string|null $str
     * @return array
     */
    public static function urlQueryParamList($str)
    {
        $list = [];

        if ($str === null)
            return $list;

        $parts = explode('?', $str);

        if (count($parts) < 2)
            return $list;

        if (trim($parts[1]) === '')
            return $list;

        parse_str($parts[1], $list);

        return $list;
    }

    /**
     * Find text match in string using regex
     * - (?:.*) Non matching group
     * - (.*?)" Match until first occurrence
     *
     * @param string $str
     * @param string $re
     * @param int $group
     * @param int $matchIndex
     *
     * @return false|string
     */
    public static function regex(string $str, string $re, $matchIndex = 0, $group = 1)
    {
        $matches = self::regexAll($str, $re);

        if (count($matches) === 0)
            return false;

        $match = $matches[$matchIndex];

        return ArrayHandler::hasKey($match, $group) ? $match[$group] : $match[0];
    }

    /**
     * @param string $str
     * @param string $re
     * @param mixed $value
     * @param int $group
     *
     * @return string
     */
    public static function regexReplace(string $str, string $re, $value, $group = 0)
    {
        $matches = self::regexAll($str, $re);

        foreach ($matches as $matchIndex => $groupList) {
            $groupValue = ArrayHandler::hasKey($groupList, $group) ? $groupList[$group] : $groupList[0];

            $str = str_replace($groupValue, $value, $str);
        }

        return $str;
    }

    /**
     * @param string $str
     * @param string $re
     *
     * @return array
     */
    public static function regexAll(string $str, string $re)
    {
        $matches = [];

        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

        return $matches;
    }

    /**
     * Alias for Transliterator::transform
     *
     * @param string $str
     * @param string $spaceReplace
     * @param bool $toLowerCase
     *
     * @return string
     */
    public static function transliterate(string $str, $spaceReplace = '-', $toLowerCase = true)
    {
        return Transliterator::transform($str, $spaceReplace, $toLowerCase);
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public static function camelCaseToUnderscore(string $str)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $str)), '_');
    }

    /**
     * @param string $str
     * @param bool $firstLetterBig
     *
     * @return string
     */
    public static function underscoreToCamelCase(string $str, bool $firstLetterBig = false)
    {
        $separator = '_';

        $str = str_replace($separator, '', ucwords($str, $separator));

        if ($firstLetterBig === false)
            $str[0] = mb_strtolower($str[0]);

        return $str;
    }

    /**
     * @param string $str
     * @param string|string[] $charList
     *
     * @return string
     */
    public static function removeFirstChars(string $str, string $charList)
    {
        if (VarHandler::isArray($charList))
            $charList = join('', $charList);

        return ltrim($str, $charList);
    }

    /**
     * @param string $str
     * @param string|string[] $charList
     *
     * @return string
     */
    public static function removeLastChars(string $str, string $charList)
    {
        if (VarHandler::isArray($charList))
            $charList = join('', $charList);

        return rtrim($str, $charList);
    }

}