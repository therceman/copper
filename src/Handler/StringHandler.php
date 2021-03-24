<?php


namespace Copper\Handler;


use Copper\Transliterator;

class StringHandler
{

    /**
     * @param string|int|null|bool $str
     *
     * @return bool
     */
    public static function isNotEmpty($str)
    {
        return (self::isEmpty($str) === false);
    }

    /**
     * @param string|int|null|bool $str
     *
     * @return bool
     */
    public static function isEmpty($str)
    {
        return (trim($str) === '' || $str === null || $str === false);
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

        return count($matches) > 0 ? $matches[$matchIndex][$group] : false;
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
            $groupValue = $groupList[$group];

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
        if (is_array($charList))
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
        if (is_array($charList))
            $charList = join('', $charList);

        return rtrim($str, $charList);
    }
}