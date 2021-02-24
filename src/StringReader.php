<?php


namespace Copper;


class StringReader
{

    public static function urlQueryParamList(string $str)
    {
        $list = [];

        parse_str(ArrayReader::lastValue(explode('?', $str)), $list);

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
    public static function regex(string $str, string $re, $group = 0, $matchIndex = 0)
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