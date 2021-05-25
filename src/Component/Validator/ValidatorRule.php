<?php


namespace Copper\Component\Validator;


use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;

/**
 * Class ValidatorRule
 * @package Copper\Component\Validator
 */
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

    const NUMERIC = 19;

    /** @var string */
    public $name;
    /** @var int */
    public $type;
    /** @var int */
    public $maxLength;
    /** @var int */
    public $minLength;
    /** @var int */
    public $length;
    /** @var bool */
    public $required;
    /** @var bool */
    public $strict;
    /** @var null|string */
    public $regex;
    /** @var null|array */
    public $filterValues;
    /** @var bool */
    public $blacklistFilter;

    /**
     * ValidatorRule constructor.
     *
     * @param string $name
     * @param int $type
     * @param bool $maxLength
     * @param bool $required
     */
    public function __construct(string $name, $type = self::STRING, $maxLength = false, $required = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->maxLength = $maxLength;
        $this->required = $required;

        $this->minLength = 0;
        $this->length = false;
        $this->filterValues = null;
        $this->regex = null;
        $this->blacklistFilter = false;
    }

    /**
     * @param $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param int $max
     * @return $this
     */
    public function maxLength(int $max)
    {
        $this->maxLength = $max;

        return $this;
    }

    /**
     * @param int $min
     * @return $this
     */
    public function minLength(int $min)
    {
        $this->minLength = $min;

        return $this;
    }

    /**
     * @param int $len
     * @return $this
     */
    public function length(int $len)
    {
        $this->length = $len;

        return $this;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function required($bool = true)
    {
        $this->required = $bool;

        return $this;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function strict($bool = true)
    {
        $this->strict = $bool;

        return $this;
    }

    /**
     * @param string|null $regex
     * @return $this
     */
    public function regex(?string $regex)
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * @param array|null $values
     * @param false $blacklist
     *
     * @return $this
     */
    public function filterValues(?array $values, $blacklist = false)
    {
        $this->filterValues = $values;
        $this->blacklistFilter = $blacklist;

        return $this;
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function string(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::STRING, $maxLength, $required);
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function integer(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::INTEGER, $maxLength, $required);
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function integerPositive(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::INTEGER_POSITIVE, $maxLength, $required);
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function integerNegative(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::INTEGER_NEGATIVE, $maxLength, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public static function boolean(string $name, $required = false, $strict = false)
    {
        return new self($name, self::BOOLEAN, false, $required);
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function float(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::FLOAT, $maxLength, $required);
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function floatPositive(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::FLOAT_POSITIVE, $maxLength, $required);
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function floatNegative(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::FLOAT_NEGATIVE, $maxLength, $required);
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function email(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::EMAIL, $maxLength, $required);
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function phone(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::PHONE, $maxLength, $required);
    }

    /**
     * @param string $name
     * @param array|null $values
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function enum(string $name, $values = null, $required = false)
    {
        return (new self($name, self::ENUM, false, $required))->filterValues($values);
    }

    /**
     * @param string $name
     * @param int $maxDigits
     * @param int $maxDecimals
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function decimal(string $name, int $maxDigits, int $maxDecimals, $required = false)
    {
        return (new self($name, self::DECIMAL, false, $required))->filterValues([$maxDigits, $maxDecimals]);
    }

    /**
     * @param string $name
     * @param int $maxDigits
     * @param int $maxDecimals
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function decimalPositive(string $name, int $maxDigits, int $maxDecimals, $required = false)
    {
        return (new self($name, self::DECIMAL_POSITIVE, false, $required))->filterValues([$maxDigits, $maxDecimals]);
    }

    /**
     * @param string $name
     * @param int $maxDigits
     * @param int $maxDecimals
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function decimalNegative(string $name, int $maxDigits, int $maxDecimals, $required = false)
    {
        return (new self($name, self::DECIMAL_NEGATIVE, false, $required))->filterValues([$maxDigits, $maxDecimals]);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function date(string $name, $required = false)
    {
        return new self($name, self::DATE, false, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function time(string $name, $required = false)
    {
        return new self($name, self::TIME, false, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function datetime(string $name, $required = false)
    {
        return new self($name, self::DATETIME, false, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function year(string $name, $required = false)
    {
        return new self($name, self::YEAR, false, $required);
    }

    /**
     * @param string $name
     * @param bool $maxLength
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function numeric(string $name, $maxLength = false, $required = false)
    {
        return new self($name, self::NUMERIC, $maxLength, $required);
    }

    /**
     * @param mixed $value
     * @return FunctionResponse
     */
    private function validateValueLength($value)
    {
        $res = new FunctionResponse();

        $value = strval($value);

        if ($this->minLength > 0 && strlen($value) < $this->minLength)
            return $res->error('min_length', $this->minLength);

        if ($this->maxLength !== false && strlen($value) > $this->maxLength)
            return $res->error('max_length', $this->maxLength);

        if ($this->length !== false && strlen($value) !== $this->length)
            return $res->error('length', $this->length);

        return $res->ok();
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function validateValueRequired($value)
    {
        if ($this->required === false)
            return true;

        if ($value === null)
            return false;

        return true;
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateString($value)
    {
        return VarHandler::isString($value);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateInteger($value)
    {
        return VarHandler::isInt($value);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateBoolean($value)
    {
        return VarHandler::isBoolean($value, $this->strict);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateFloat($value)
    {
        return VarHandler::isFloat($value);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateNumeric($value)
    {
        return VarHandler::isNumeric($value);
    }

    /**
     * @param $params
     * @param $name
     *
     * @return FunctionResponse
     */
    public function validate($params, $name)
    {
        $res = new FunctionResponse();

        $value = ArrayHandler::hasKey($params, $name) ? $params[$name] : null;

        if ($this->validateValueRequired($value) === false)
            return $res->error('value is required');

        $lengthValidationRes = $this->validateValueLength($value);

        if ($lengthValidationRes->hasError())
            return $res->error('wrong length', $lengthValidationRes);

        /** @var bool|null $typeValidationStatus */
        $typeValidationStatus = null;

        switch ($this->type) {
            case self::STRING:
                $typeValidationStatus = $this->validateString($value);
                break;
            case self::INTEGER:
                $typeValidationStatus = $this->validateInteger($value);
                break;
            case self::BOOLEAN:
                $typeValidationStatus = $this->validateBoolean($value);
                break;
            case self::FLOAT:
                $typeValidationStatus = $this->validateFloat($value);
                break;
            case self::NUMERIC:
                $typeValidationStatus = $this->validateNumeric($value);
        }

        if ($typeValidationStatus === null)
            return $res->error('wrong validation type');

        if ($typeValidationStatus === false)
            return $res->error('type validation failed');

        return $res->ok();
    }
}