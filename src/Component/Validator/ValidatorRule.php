<?php


namespace Copper\Component\Validator;


class ValidatorRule
{
    const STRING = 1;
    const INTEGER = 2;
    const BOOLEAN = 3;
    const FLOAT = 4;

    const EMAIL = 5;
    const PHONE = 6;
    const ENUM = 7;
    const DECIMAL = 8;

    const INTEGER_POSITIVE = 9;
    const FLOAT_POSITIVE = 10;
    const DECIMAL_POSITIVE = 11;

    const INTEGER_NEGATIVE = 12;
    const FLOAT_NEGATIVE = 13;
    const DECIMAL_NEGATIVE = 14;

    const DATE = 15;
    const TIME = 16;
    const DATETIME = 17;
    const YEAR = 18;

    const REGEX_RULES = [
        self::TIME => '/-?(\d{1,3}:\d{1,2}:\d{1,2})/m',
        self::YEAR => '/(\d{4})/m',
    ];

    /** @var string */
    public $name;
    /** @var integer */
    public $type;
    /** @var integer */
    public $length;
    /** @var boolean */
    public $required;

    /** @var boolean|string */
    public $regex;

    /** @var boolean|array */
    public $filterValues;
    /** @var boolean */
    public $blacklistFilter;

    public function __construct($name, $type = self::STRING, $length = false, $required = false, $regex = false, $filterValues = false, $blacklistFilter = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->required = $required;
        $this->regex = $regex;
        $this->filterValues = $filterValues;
        $this->blacklistFilter = $blacklistFilter;
    }

    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    public function length($length)
    {
        $this->length = $length;

        return $this;
    }

    public function required()
    {
        $this->required = true;

        return $this;
    }

    public function regex($regex)
    {
        $this->regex = $regex;

        return $this;
    }

    public function filterValues($values, $blacklist = false)
    {
        $this->filterValues = $values;
        $this->blacklistFilter = $blacklist;

        return $this;
    }

    public static function string($name, $length = false, $required = false)
    {
        return new self($name, self::STRING, $length, $required);
    }

    public static function integer($name, $length = false, $required = false)
    {
        return new self($name, self::INTEGER, $length, $required);
    }

    public static function integerPositive($name, $length = false, $required = false)
    {
        return new self($name, self::INTEGER_POSITIVE, $length, $required);
    }

    public static function integerNegative($name, $length = false, $required = false)
    {
        return new self($name, self::INTEGER_NEGATIVE, $length, $required);
    }

    public static function boolean($name, $required = false)
    {
        return new self($name, self::BOOLEAN, false, $required);
    }

    public static function float($name, $length = false, $required = false)
    {
        return new self($name, self::FLOAT, $length, $required);
    }

    public static function floatPositive($name, $length = false, $required = false)
    {
        return new self($name, self::FLOAT_POSITIVE, $length, $required);
    }

    public static function floatNegative($name, $length = false, $required = false)
    {
        return new self($name, self::FLOAT_NEGATIVE, $length, $required);
    }

    public static function email($name, $length = false, $required = false)
    {
        return new self($name, self::EMAIL, $length, $required);
    }

    public static function phone($name, $length = false, $required = false)
    {
        return new self($name, self::PHONE, $length, $required);
    }

    public static function enum($name, $values = false, $required = false)
    {
        return new self($name, self::ENUM, false, $required, false, $values);
    }

    public static function decimal($name, $maxDigits, $maxDecimals, $required = false)
    {
        return new self($name, self::DECIMAL, false, $required, false, [$maxDigits, $maxDecimals]);
    }

    public static function decimalPositive($name, $maxDigits, $maxDecimals, $required = false)
    {
        return new self($name, self::DECIMAL_POSITIVE, false, $required, false, [$maxDigits, $maxDecimals]);
    }

    public static function decimalNegative($name, $maxDigits, $maxDecimals, $required = false)
    {
        return new self($name, self::DECIMAL_NEGATIVE, false, $required, false, [$maxDigits, $maxDecimals]);
    }

    public static function date($name, $required = false)
    {
        return new self($name, self::DATE, false, $required);
    }

    public static function time($name, $required = false)
    {
        return new self($name, self::TIME, false, $required);
    }

    public static function datetime($name, $required = false)
    {
        return new self($name, self::DATETIME, false, $required);
    }

    public static function year($name, $required = false)
    {
        return new self($name, self::YEAR, false, $required);
    }

}