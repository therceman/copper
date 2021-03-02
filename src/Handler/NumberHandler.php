<?php


namespace Copper\Handler;


class NumberHandler
{
    const SIGN_USD = '$';
    const SIGN_EUR = '€';
    const SIGN_PERCENT = '%';

    public static function format($num, $dec = 2, $d_sep = '.', $t_sep = '')
    {
        return number_format($num, $dec, $d_sep, $t_sep);
    }

    public static function currencyFormat($num, $currency = self::SIGN_EUR, $dec = 2, $d_sep = '.', $t_sep = '')
    {
        return self::format($num, $dec, $d_sep, $t_sep) . ' ' . $currency;
    }

    public static function percentFormat($num, $dec = 2, $d_sep = '.', $t_sep = '')
    {
        return self::format($num, $dec, $d_sep, $t_sep) . ' ' . self::SIGN_PERCENT;
    }
}