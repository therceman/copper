<?php


namespace Copper\Component\Validator;


use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;
use Copper\Kernel;

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
    const ALPHA = 20;
    const ALPHA_NUMERIC = 21;

    /** @var string */
    public $name;
    /** @var int */
    public $type;
    /** @var int|null */
    public $maxLength;
    /** @var int */
    public $minLength;
    /** @var int|null */
    public $length;
    /** @var bool */
    public $required;
    /** @var bool */
    public $strict;
    /** @var string|null */
    public $regex;
    /** @var array|null */
    public $filterValues;
    /** @var bool */
    public $blacklistFilter;
    /** @var bool */
    public $allowAlphaSpaces;
    /** @var string|null */
    public $regexFormatExample;

    /**
     * ValidatorRule constructor.
     *
     * @param string $name
     * @param int $type
     * @param bool $required
     */
    public function __construct(string $name, $type = self::STRING, $required = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;

        $this->minLength = 0;
        $this->maxLength = null;
        $this->length = null;
        $this->filterValues = null;
        $this->regex = null;
        $this->blacklistFilter = false;
        $this->allowAlphaSpaces = true;
        $this->regexFormatExample = null;
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
     * @param int|null $max
     * @return $this
     */
    public function maxLength(?int $max)
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
     * @param int|null $len
     * @return $this
     */
    public function length(?int $len)
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
     * @param bool $bool
     * @return $this
     */
    public function allowAlphaSpaces($bool = true)
    {
        $this->allowAlphaSpaces = $bool;

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
     * @param string|null $example
     * @return $this
     */
    public function regexFormatExample(?string $example)
    {
        $this->regexFormatExample = $example;

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
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function string(string $name, $required = false)
    {
        return new self($name, self::STRING, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function integer(string $name, $required = false)
    {
        return new self($name, self::INTEGER, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function integerPositive(string $name, $required = false)
    {
        return new self($name, self::INTEGER_POSITIVE, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function integerNegative(string $name, $required = false)
    {
        return new self($name, self::INTEGER_NEGATIVE, $required);
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
        return (new self($name, self::BOOLEAN, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function float(string $name, $required = false)
    {
        return new self($name, self::FLOAT, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function floatPositive(string $name, $required = false)
    {
        return new self($name, self::FLOAT_POSITIVE, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function floatNegative(string $name, $required = false)
    {
        return new self($name, self::FLOAT_NEGATIVE, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function email(string $name, $required = false)
    {
        $rule = new self($name, self::EMAIL, $required);

        if (Kernel::getValidator() === null)
            return $rule;

        $validatorConfig = Kernel::getValidator()->config;

        $rule->minLength($validatorConfig->email_minLength);
        $rule->maxLength($validatorConfig->email_maxLength);
        $rule->regex($validatorConfig->email_regex);
        $rule->regexFormatExample($validatorConfig->email_regex_format_example);

        return $rule;
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function phone(string $name, $required = false)
    {
        return new self($name, self::PHONE, $required);
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
        return (new self($name, self::ENUM, $required))->filterValues($values);
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
        return (new self($name, self::DECIMAL, $required))->filterValues([$maxDigits, $maxDecimals]);
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
        return (new self($name, self::DECIMAL_POSITIVE, $required))->filterValues([$maxDigits, $maxDecimals]);
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
        return (new self($name, self::DECIMAL_NEGATIVE, $required))->filterValues([$maxDigits, $maxDecimals]);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function date(string $name, $required = false)
    {
        return new self($name, self::DATE, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function time(string $name, $required = false)
    {
        return new self($name, self::TIME, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function datetime(string $name, $required = false)
    {
        return new self($name, self::DATETIME, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function year(string $name, $required = false)
    {
        return new self($name, self::YEAR, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function numeric(string $name, $required = false)
    {
        return new self($name, self::NUMERIC, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $allowSpaces
     *
     * @return ValidatorRule
     */
    public static function alpha(string $name, $required = false, $allowSpaces = true)
    {
        return (new self($name, self::ALPHA, $required))->allowAlphaSpaces($allowSpaces);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $allowSpaces
     *
     * @return ValidatorRule
     */
    public static function alphaNumeric(string $name, $required = false, $allowSpaces = true)
    {
        return (new self($name, self::ALPHA_NUMERIC, $required))->allowAlphaSpaces($allowSpaces);
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
            return $res->error('minLengthRequired', $this->minLength);

        if ($this->maxLength !== null && strlen($value) > $this->maxLength)
            return $res->error('maxLengthReached', $this->maxLength);

        if ($this->length !== null && strlen($value) !== $this->length)
            return $res->error('wrongLength', $this->length);

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

        if (VarHandler::isString($value) && StringHandler::trim($value) === '')
            return false;

        if ($value === null)
            return false;

        return true;
    }

    /**
     * @param string $value
     * @return FunctionResponse
     */
    private function validateValueRegex($value)
    {
        $res = new FunctionResponse();

        if (VarHandler::isString($value) === false)
            return $res->error('wrongValueType', ["string", VarHandler::getType($value)]);

        if ($this->regex === null)
            return $res->ok();

        $regexResult = StringHandler::regex($value, $this->regex);

        if ($regexResult === false)
            return $res->error('invalidValueFormat', ['example' => $this->regexFormatExample]);

        return $res->ok();
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
     * @param $value
     * @return bool
     */
    private function validateAlpha($value)
    {
        return VarHandler::isAlpha($value, $this->allowAlphaSpaces);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateAlphaNumeric($value)
    {
        return VarHandler::isAlphaNumeric($value, $this->allowAlphaSpaces);
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

        // required

        if ($this->validateValueRequired($value) === false)
            return $res->error('valueCannotBeEmpty');

        // length

        $lengthValidationRes = $this->validateValueLength($value);
        if ($lengthValidationRes->hasError())
            return $res->error($lengthValidationRes->msg, $lengthValidationRes->result);

        // regex

        $regexValidationRes = $this->validateValueRegex($value);
        if ($regexValidationRes->hasError())
            return $res->error($regexValidationRes->msg, $regexValidationRes->result);

        // type

        /** @var bool|null $typeValidationStatus */
        $typeValidationStatus = null;

        switch ($this->type) {
            case self::STRING:
                if ($this->validateString($value) === false)
                    return $res->error('wrongValueType', ['string', VarHandler::getType($value)]);
                break;
            case self::INTEGER:
                if ($this->validateInteger($value) === false)
                    return $res->error('wrongValueType', ['integer', VarHandler::getType($value)]);
                break;
            case self::BOOLEAN:
                if ($this->validateBoolean($value) === false)
                    return $res->error('wrongValueType', ['boolean', VarHandler::getType($value)]);
                break;
            case self::FLOAT:
                if ($this->validateFloat($value) === false)
                    return $res->error('wrongValueType', ['float', VarHandler::getType($value)]);
                break;
            case self::NUMERIC:
                if ($this->validateNumeric($value) === false)
                    return $res->error('valueTypeIsNotNumeric');
                break;
            case self::ALPHA:
                if ($this->validateAlpha($value) === false)
                    return $res->error('valueTypeIsNotAlphabetic', ($this->allowAlphaSpaces));
                break;
            case self::ALPHA_NUMERIC:
                if ($this->validateAlphaNumeric($value) === false)
                    return $res->error('valueTypeIsNotAlphabeticOrNumeric', ($this->allowAlphaSpaces));
                break;
            case self::EMAIL:
                return $res->ok();
            default:
                return $res->error('wrongValidationType');
        }

        return $res->ok();
    }
}