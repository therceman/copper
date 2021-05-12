<?php


namespace Copper\Handler;


class NumberHandler
{
    const SIGN_USD = '$';
    const SIGN_EUR = '€';
    const SIGN_PERCENT = '%';

    /**
     * Returns absolute value
     * <hr>
     * <code>
     * - absolute(-1)    // 1
     * - absolute(-1.33) // 1.33
     * - absolute(1.33)  // 1.33
     * </code>
     * @param float|int $num
     * @return float|int
     */
    public static function absolute($num)
    {
        return abs($num);
    }

    public static function divisionRemainder($num1, $num2)
    {
        return $num1 % $num2;
    }

    public static function round($num, $dec = 2)
    {
        return round($num, $dec);
    }

    public static function leadingZeros($num, $count)
    {
        return str_pad($num, $count, '0', STR_PAD_LEFT);
    }

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

    public static function random($min = 0, $max = null)
    {
        return rand($min, $max);
    }
}