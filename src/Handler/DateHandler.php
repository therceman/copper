<?php


namespace Copper\Handler;


class DateHandler
{

    /**
     * @param false $twoDigits
     * @return int
     */
    public static function year($twoDigits = false)
    {
        return (int)date(($twoDigits) ? 'y' : 'Y');
    }

    /**
     * @return int
     */
    public static function day()
    {
        return (int)date('d');
    }

    /**
     * @return int
     */
    public static function month()
    {
        return (int)date('m');
    }
}