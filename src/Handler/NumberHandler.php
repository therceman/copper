<?php


namespace Copper\Handler;


/**
 * Class NumberHandler
 * @package Copper\Handler
 */
class NumberHandler
{
    public const SIGN_USD = '$';
    public const SIGN_EUR = '€';
    public const SIGN_PERCENT = '%';

    // The float data type can commonly store a value up to 1.7976931348623E+308 (platform dependent),
    // and have a maximum precision of 14 digits.
    public const MAX_FLOAT_PRECISION = 14;

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

    /**
     * Add percent to number
     *
     * @param float|int $num
     * @param float|int $perc
     * @return float|int
     */
    public static function addPercent($num, $perc)
    {
        return $num - (($num * $perc) / 100);
    }

    /**
     * Subtract percent from number
     *
     * @param float|int $num
     * @param float|int $perc
     * @return float|int
     */
    public static function subPercent($num, $perc)
    {
        return $num + (($num * $perc) / 100);
    }

    /**
     * Calculate price without discount
     *
     * @param float|int $currentPrice Price, e.g. 1.25
     * @param float|int $discount Discount in percents, e.g. 25%
     * @return float|int
     */
    public static function priceWithoutDiscount($currentPrice, $discount)
    {
        return $currentPrice * (100 / (100 - $discount));
    }

    /**
     * @param float|int $num1
     * @param float|int $num2
     * @return int
     */
    public static function divisionRemainder($num1, $num2)
    {
        return $num1 % $num2;
    }

    /**
     * @param $num
     * @param int $dec
     * @return float
     */
    public static function round($num, $dec = 2)
    {
        $dec = $dec > self::MAX_FLOAT_PRECISION ? self::MAX_FLOAT_PRECISION : $dec;

        return round($num, $dec);
    }

    /**
     * Add leading zeros to the number
     * <hr>
     * <code>
     * - leadingZeros(1, 2) // 01
     * - leadingZeros(1, 3) // 001
     * </code>
     * @param $num
     * @param $length
     * @return string
     */
    public static function leadingZeros($num, $length)
    {
        return str_pad($num, $length, '0', STR_PAD_LEFT);
    }

    /**
     * @param int|float $num
     * @param int $dec
     * @param string $d_sep
     * @param string $t_sep
     * @return string
     */
    public static function format($num, $dec = 2, $d_sep = '.', $t_sep = '')
    {
        if (is_string($num))
            $num = self::round($num, $dec);

        $dec = $dec > self::MAX_FLOAT_PRECISION ? self::MAX_FLOAT_PRECISION : $dec;

        return number_format($num, $dec, $d_sep, $t_sep);
    }

    /**
     * @param int|float $num
     * @param string $currency
     * @param int $dec
     * @param string $d_sep
     * @param string $t_sep
     * @return string
     */
    public static function currencyFormat($num, $currency = self::SIGN_EUR, $dec = 2, $d_sep = '.', $t_sep = '')
    {
        return self::format($num, $dec, $d_sep, $t_sep) . ' ' . $currency;
    }

    /**
     * @param int|float $num
     * @param int $dec
     * @param string $d_sep
     * @param string $t_sep
     * @return string
     */
    public static function percentFormat($num, $dec = 2, $d_sep = '.', $t_sep = '')
    {
        return self::format($num, $dec, $d_sep, $t_sep)  . self::SIGN_PERCENT;
    }

    /**
     * @param int $min
     * @param int|null $max
     * @return int
     */
    public static function random($min = 0, $max = null)
    {
        return rand($min, $max);
    }
}