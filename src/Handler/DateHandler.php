<?php


namespace Copper\Handler;


use Copper\Kernel;
use DateTime;
use DateTimeZone;

class DateHandler
{
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';
    const TIME_NO_SEC_FORMAT = 'H:i';
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    const EUROPE_DATE_FORMAT = 'd.m.Y';
    const EUROPE_DATE_TIME_FORMAT = 'd.m.Y H:i:s';
    const EUROPE_DATE_TIME_NO_SEC_FORMAT = 'd.m.Y H:i';

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

    /**
     * @param null $timezone
     * @return int
     */
    public static function timestamp($timezone = null)
    {
        return self::newDate()->getTimestamp();
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
        $configTimezone = Kernel::getApp()->config->timezone ?? null;

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

    public static function isValidTimezone($timezone): bool
    {
        try {
            new DateTimeZone($timezone);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $date
     * @param string $format
     *
     * @return bool
     */
    private static function isValid(string $date, string $format)
    {
        $formattedDate = self::formatDate($date, $format, $format);

        if ($formattedDate === false)
            return false;

        return $formattedDate === $date;
    }

    /**
     * @param int|string $year
     * @param int|string $month
     * @param int|string $day
     *
     * @return bool
     */
    public static function isValidSplitDate($year, $month, $day)
    {
        $year = (int)$year;
        $month = (int)$month;
        $day = (int)$day;

        if ($year > 9999 || $year < 1000)
            return false;

        return checkdate($month, $day, $year);
    }

    /**
     * Check if date is valid. E.g. 2020-01-01
     *
     * @param string $date
     * @param string|null $format
     *
     * @return bool
     */
    public static function isDateValid(string $date, $format = null)
    {
        $format = ($format === null) ? self::getDateFormat() : $format;

        return self::isValid($date, $format);
    }

    /**
     * Check if time is valid. E.g. 12:53:49
     *
     * @param string $time
     * @param string|null $format
     *
     * @return bool
     */
    public static function isTimeValid(string $time, $format = null)
    {
        $format = ($format === null) ? self::getTimeFormat() : $format;

        return self::isValid($time, $format);
    }

    /**
     * Check if datetime is valid. E.g. 2020-01-01 12:53:49
     *
     * @param string $time
     * @param string|null $format
     *
     * @return bool
     */
    public static function isDateTimeValid(string $time, $format = null)
    {
        $format = ($format === null) ? self::getDateTimeFormat() : $format;

        return self::isValid($time, $format);
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
     * @param string $toFormat
     * @param string|null $fromFormat
     *
     * @return false|string
     */
    public static function formatDate(string $date, string $toFormat, string $fromFormat = null)
    {

        $fromFormat = ($fromFormat === null) ? self::getDateFormat() : $fromFormat;

        $date = DateTime::createFromFormat($fromFormat, $date);

        if ($date === false)
            return false;

        return $date->format($toFormat);
    }

    /**
     * @param string $time
     * @param string $toFormat
     * @param string|null $fromFormat
     *
     * @return false|string
     */
    public static function formatTime(string $time, string $toFormat, string $fromFormat = null)
    {

        $fromFormat = ($fromFormat === null) ? self::getTimeFormat() : $fromFormat;

        $date = DateTime::createFromFormat($fromFormat, $time);

        if ($date === false)
            return false;

        return $date->format($toFormat);
    }

    /**
     * @param string $date
     * @param string $toFormat
     * @param string|null $fromFormat
     *
     * @return false|string
     */
    public static function formatDateTime(string $date, string $toFormat, string $fromFormat = null)
    {

        $fromFormat = ($fromFormat === null) ? self::getDateTimeFormat() : $fromFormat;

        $date = DateTime::createFromFormat($fromFormat, $date);

        if ($date === false)
            return false;

        return $date->format($toFormat);
    }

    /**
     * Datetime from timestamp
     *
     * @param int $timestamp
     * @param null $toFormat
     * @return false|string
     */
    public static function dateTimeFromTimestamp(int $timestamp, $toFormat = null)
    {
        $toFormat = ($toFormat === null) ? self::getDateTimeFormat() : $toFormat;

        return date($toFormat, $timestamp);
    }

    /**
     * Date from timestamp
     *
     * @param int $timestamp
     * @param null $toFormat
     * @return false|string
     */
    public static function dateFromTimestamp(int $timestamp, $toFormat = null)
    {
        $toFormat = ($toFormat === null) ? self::getDateFormat() : $toFormat;

        return date($toFormat, $timestamp);
    }

    /**
     * @param string $date
     * @param string $fromFormat
     * @param string|null $toFormat
     *
     * @return string|false
     */
    public static function dateFromFormat(string $date, string $fromFormat, $toFormat = null)
    {
        $toFormat = ($toFormat === null) ? self::getDateFormat() : $toFormat;

        $date = DateTime::createFromFormat($fromFormat, $date);

        if ($date === false)
            return false;

        return $date->format($toFormat);
    }
}