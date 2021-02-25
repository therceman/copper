<?php


namespace Copper\Handler;


use Copper\Transliterator;

class StringHandler
{

    public static function urlQueryParamList(string $str)
    {
        $list = [];

        $parts = explode('?', $str);

        if (count($parts) === 2 && trim($parts[1]) === '')
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
    public static function regex(string $str, string $re, $group = 0, $matchIndex = 1)
    {
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

        return count($matches) > 0 ? $matches[$group][$matchIndex] : false;
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
}