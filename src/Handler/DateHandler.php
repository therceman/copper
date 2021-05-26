<?php


namespace Copper\Handler;


use Copper\Kernel;
use DateTime;
use DateTimeZone;

class DateHandler
{
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public static function getDateFormat()
    {
        if (Kernel::getApp() !== null)
            return Kernel::getApp()->config->dateFormat;

        return self::DATE_FORMAT;
    }

    public static function getTimeFormat()
    {
        if (Kernel::getApp() !== null)
            return Kernel::getApp()->config->timeFormat;

        return self::TIME_FORMAT;
    }

    public static function getDateTimeFormat()
    {
        if (Kernel::getApp() !== null)
            return Kernel::getApp()->config->dateTimeFormat;

        return self::DATE_TIME_FORMAT;
    }

    /**
     * @param string|null $timezone
     *
     * @return DateTime
     */
    private static function newDate($timezone = null)
    {
        $configTimezone = Kernel::getApp()->config->timezone;

        if ($timezone === null && $configTimezone !== false)
            $timezone = $configTimezone;

        if ($timezone === null)
            return new DateTime("now");

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
     * @param string|null $timezone
     * @param string|null $format
     *
     * @return false|string
     */
    public static function date($timezone = null, $format = null)
    {
        $format = ($format === null) ? self::getDateFormat() : $format;

        return self::newDate($timezone)->format($format);
    }

    /**
     * @return array
     */
    public static function dayList()
    {
        return ArrayHandler::fromRange(1, 31);
    }

    /**
     * @return array
     */
    public static function monthList()
    {
        return ArrayHandler::fromRange(1, 12);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function yearList($limit = 100, $offset = 0)
    {
        return ArrayHandler::fromRange(DateHandler::year() - $limit, DateHandler::year() - $offset);
    }

    /**
     * @param string|null $timezone
     * @param string|null $format
     * @return false|string
     */
    public static function time($timezone = null, $format = null)
    {
        $format = ($format === null) ? self::getTimeFormat() : $format;

        return self::date($timezone, $format);
    }

    /**
     * @param string|null $timezone
     * @param string|null $format
     * @return false|string
     */
    public static function dateTime($timezone = null, $format = null)
    {
        $format = ($format === null) ? self::getDateTimeFormat() : $format;

        return self::date($timezone, $format);
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     * @param string|null $date [default] = current date
     * @param bool $include
     * @return bool
     */
    public static function isBetweenDates(string $dateFrom, string $dateTo, $date = null, $include = true)
    {
        if ($date === null)
            $date = self::date();

        $hasStarted = false;
        if ($include && self::dateDiff($date, $dateFrom) <= 0 || $include === false && self::dateDiff($date, $dateFrom) < 0)
            $hasStarted = true;

        $hasEnded = false;
        if ($include && self::dateDiff($date, $dateTo) < 0 || $include === false && self::dateDiff($date, $dateTo) <= 0)
            $hasEnded = true;

        if ($hasStarted && $hasEnded === false)
            return true;

        return false;
    }

    /**
     * Returns the difference between two dates
     * <hr>
     * <code>
     * // to date
     * - dateDiff('2021-10-05')                 // 1 (if current date is 2021-10-04)
     * - dateDiff(null, '2021-10-06')           // 2 (if current date is 2021-10-04)
     * // from date - to date
     * - dateDiff('2021-10-05', '2021-10-08')   // 3
     * </code>
     * @param string|null $dateA
     * @param string|null $dateB
     * @param string|null $format [optional] = Y-m-d
     *
     * @return int
     */
    public static function dateDiff(?string $dateA, $dateB = null, $format = null)
    {
        $format = ($format === null) ? self::getDateFormat() : $format;

        if ($dateA === null)
            $dateA = self::date();

        if ($dateB === null) {
            $dateB = $dateA;
            $dateA = self::date();
        }

        $dateA = DateTime::createFromFormat($format, $dateA);
        $dateB = DateTime::createFromFormat($format, $dateB);

        $interval = date_diff($dateA, $dateB);

        $format = $interval->format('%r%a');

        return (int)$format;
    }

    /**
     * @param string $date
     * @param string $fromFormat
     * @param string|null $toFormat
     *
     * @return string
     */
    public static function fromFormat(string $date, string $fromFormat, $toFormat = null)
    {
        $toFormat = ($toFormat === null) ? self::getDateFormat() : $toFormat;

        return DateTime::createFromFormat($fromFormat, $date)->format($toFormat);
    }
}