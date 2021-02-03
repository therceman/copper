<?php


namespace Copper;


class StringReader
{
    public static function regex($str, $re, $group = 0, $matchIndex = 0)
    {
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

        return count($matches) > 0 ? $matches[0][0] : false;
    }
}