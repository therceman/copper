<?php


namespace Copper\Handler;


use DateTime;
use DateTimeZone;

class DateHandler
{
    // TODO move this configuration to App Config -> app.php

    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const TIMEZONE = 'Europe/Riga';

    /**
     * @param string $timezone
     *
     * @return DateTime
     */
    private static function newDate($timezone = self::TIMEZONE)
    {
        try {
            return new DateTime("now", new DateTimeZone($timezone));
        } catch (\Exception $e) {
            return new DateTime("now");
        }
    }

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

    /**
     * @param string $timezone
     * @param string $format
     *
     * @return false|string
     */
    public static function date($timezone = self::TIMEZONE, $format = self::DATE_FORMAT)
    {
        return self::newDate($timezone)->format($format);
    }

    /**
     * @param string $timezone
     *
     * @return false|string
     */
    public static function time($timezone = self::TIMEZONE)
    {
        return self::date($timezone, self::TIME_FORMAT);
    }

    /**
     * @param string $timezone
     *
     * @return false|string
     */
    public static function dateTime($timezone = self::TIMEZONE)
    {
        return self::date($timezone, self::DATE_TIME_FORMAT);
    }

    /**
     * @param string $date
     * @param string $fromFormat
     * @param string $toFormat
     *
     * @return string
     */
    public static function fromFormat(string $date, string $fromFormat, $toFormat = self::DATE_FORMAT)
    {
        return DateTime::createFromFormat($fromFormat, $date)->format($toFormat);
    }
}