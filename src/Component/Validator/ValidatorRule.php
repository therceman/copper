<?php


namespace Copper\Component\Validator;


use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;
use Copper\Handler\DateHandler;
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

    const DATE = 9;
    const SPLIT_DATE = 10;
    const TIME = 11;
    const DATETIME = 12;
    const YEAR = 13;

    const NUMERIC = 14;
    const ALPHA = 15;
    const ALPHA_NUMERIC = 16;

    /** @var string */
    private $name;
    /** @var int */
    private $type;
    /** @var int|null */
    private $maxLength;
    /** @var int */
    private $minLength;
    /** @var int|null */
    private $length;
    /** @var bool */
    private $required;
    /** @var bool */
    private $strict;
    /** @var string|null */
    private $regex;
    /** @var array|null */
    private $enumList;
    /** @var bool */
    private $alphaAllowSpaces;
    /** @var string */
    private $alphaExtraCharacters;
    /** @var string|array|null */
    private $regexFormatExample;
    /** @var int|null */
    private $maxDecimals;
    /** @var bool|null */
    private $positive;
    /** @var bool|null */
    private $negative;
    /** @var string|null */
    private $dateFormat;
    /** @var string|null */
    private $timeFormat;
    /** @var string|null */
    private $dateTimeFormat;
    /** @var int|float|null */
    private $min;
    /** @var int|float|null */
    private $max;
    /** @var array|null */
    private $splitDateList;
    /** @var string|null */
    private $match;

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

        $this->match = null;

        $this->minLength = 0;
        $this->maxLength = null;
        $this->length = null;
        $this->enumList = null;
        $this->regex = null;

        $this->alphaExtraCharacters = '';
        $this->alphaAllowSpaces = true;
        $this->regexFormatExample = null;
        $this->maxDecimals = null;
        $this->positive = null;
        $this->negative = null;
        $this->dateFormat = Kernel::getValidator()->config->date_format;
        $this->timeFormat = Kernel::getValidator()->config->time_format;
        $this->dateTimeFormat = Kernel::getValidator()->config->dateTime_format;

        $this->min = null;
        $this->max = null;

        $this->splitDateList = null;

        $this->strict = Kernel::getValidator()->config->strict;
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
    public function maxLength($max = null)
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
     * @param string $match
     * @return $this
     */
    public function match(string $match)
    {
        $this->match = $match;

        return $this;
    }

    /**
     * @param int|null $len
     * @return $this
     */
    public function length($len = null)
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
     * Minimum value for numeric input
     *
     * @param int|float|null $min
     * @return $this
     */
    public function min($min = null)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * Maximum value for numeric input
     *
     * @param int|float|null $max
     * @return $this
     */
    public function max($max = null)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @param int|null $decimals
     * @return $this
     */
    public function maxDecimals($decimals = null)
    {
        $this->maxDecimals = $decimals;

        return $this;
    }

    /**
     * @param string|null $format
     * @return $this
     */
    public function dateFormat($format = null)
    {
        $this->dateFormat = $format;

        return $this;
    }

    /**
     * @param string|null $format
     * @return $this
     */
    public function timeFormat($format = null)
    {
        $this->timeFormat = $format;

        return $this;
    }

    /**
     * @param string|null $format
     * @return $this
     */
    public function dateTimeFormat($format = null)
    {
        $this->dateTimeFormat = $format;

        return $this;
    }

    /**
     * @param bool|null $bool
     * @return $this
     */
    public function positive($bool = true)
    {
        $this->positive = $bool;

        return $this;
    }

    /**
     * @param bool|null $bool
     * @return $this
     */
    public function negative($bool = true)
    {
        $this->negative = $bool;

        return $this;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function alphaAllowSpaces($bool = true)
    {
        $this->alphaAllowSpaces = $bool;

        return $this;
    }

    /**
     * @param string $characters
     * @return $this
     */
    public function alphaExtraCharacters(string $characters)
    {
        $this->alphaExtraCharacters = $characters;

        return $this;
    }

    /**
     * @param string|null $regex
     * @param string|array|null $example
     * @return $this
     */
    public function regex(?string $regex, $example = null)
    {
        $this->regex = $regex;

        if ($example !== null)
            $this->regexFormatExample = $example;

        return $this;
    }

    /**
     * @param string|array|null $example
     * @return $this
     */
    public function regexFormatExample($example)
    {
        $this->regexFormatExample = $example;

        return $this;
    }

    /**
     * @param array|null $values
     *
     * @return $this
     */
    public function enumList(?array $values)
    {
        $this->enumList = $values;

        return $this;
    }

    /**
     * @param array|null $values
     *
     * @return $this
     */
    public function splitDateList(?array $values)
    {
        $this->splitDateList = $values;

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
    public static function boolean(string $name, $required = false)
    {
        return (new self($name, self::BOOLEAN, $required));
    }

    /**
     * @param string $name
     * @param bool $required
     * @param int|null $maxDecimals
     *
     * @return ValidatorRule
     */
    public static function float(string $name, $required = false, $maxDecimals = null)
    {
        return (new self($name, self::FLOAT, $required))->maxDecimals($maxDecimals);
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
        $rule = new self($name, self::PHONE, $required);

        $validatorConfig = Kernel::getValidator()->config;

        $rule->regex($validatorConfig->phone_regex);
        $rule->regexFormatExample($validatorConfig->phone_regex_format_example);

        return $rule;
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
        return (new self($name, self::ENUM, $required))->enumList($values);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param int|null $maxDecimals
     *
     * @return ValidatorRule
     */
    public static function decimal(string $name, $required = false, $maxDecimals = null)
    {
        return (new self($name, self::DECIMAL, $required))->maxDecimals($maxDecimals);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param string|null $dateFormat
     *
     * @return ValidatorRule
     */
    public static function date(string $name, $required = false, $dateFormat = null)
    {
        return (new self($name, self::DATE, $required))->dateFormat($dateFormat);
    }

    /**
     * @param string $name
     * @param string|int $year
     * @param string|int $month
     * @param string|int $day
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public static function splitDate(string $name, $year, $month, $day, $required = false)
    {
        return (new self($name, self::SPLIT_DATE, $required))->splitDateList([$year, $month, $day]);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param string|null $timeFormat
     *
     * @return ValidatorRule
     */
    public static function time(string $name, $required = false, $timeFormat = null)
    {
        return (new self($name, self::TIME, $required))->timeFormat($timeFormat);
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
        $rule = new self($name, self::YEAR, $required);

        $rule->min(1000);
        $rule->max(9999);
        $rule->maxDecimals(0);

        return $rule;
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
        return (new self($name, self::ALPHA, $required))->alphaAllowSpaces($allowSpaces);
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
        return (new self($name, self::ALPHA_NUMERIC, $required))->alphaAllowSpaces($allowSpaces);
    }

    /**
     * @param mixed $value
     * @return FunctionResponse
     */
    private function validateValueLength($value)
    {
        $res = new FunctionResponse();

        if (VarHandler::isString($value) === false)
            $value = VarHandler::toString($value);

        if ($this->minLength > 0 && strlen($value) < $this->minLength)
            return $res->error(ValidatorHandler::MIN_LENGTH_REQUIRED, $this->minLength);

        if ($this->maxLength !== null && strlen($value) > $this->maxLength)
            return $res->error(ValidatorHandler::MAX_LENGTH_REACHED, $this->maxLength);

        if ($this->length !== null && strlen($value) !== $this->length)
            return $res->error(ValidatorHandler::WRONG_LENGTH, $this->length);

        return $res->ok();
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function validateValueRequired($value)
    {
        if (VarHandler::isString($value) && StringHandler::isEmpty($value))
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

        if ($this->regex === null)
            return $res->ok();

        if (VarHandler::isString($value) === false)
            $value = VarHandler::toString($value);

        $regexResult = StringHandler::regex($value, $this->regex);

        $errorMsg = ValidatorHandler::INVALID_VALUE_FORMAT;

        switch ($this->type) {
            case self::EMAIL:
                $errorMsg = ValidatorHandler::INVALID_EMAIL_FORMAT;
                break;
            case self::PHONE:
                $errorMsg = ValidatorHandler::INVALID_PHONE_FORMAT;
                break;
        }

        if ($regexResult === false)
            return $res->error($errorMsg, ['example' => $this->regexFormatExample]);

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
        return VarHandler::isInt($value, $this->strict);
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
     * Decimal validation.
     * <hr>
     * maxDecimals is processed here.
     * <hr>
     * Info: Be aware that PHP supports only 15 symbols long float value.
     * Everything after 15 symbols will be stripped (and if it has 16 symbol, this symbol will be rounded with 15)
     * <p>
     * For Example:
     * <code>
     * - 12345678901.001000006066046 = 12345678901.001
     * - 1234567890.001000006066046 = 1234567890.001
     * - 123456789.000100006066046 = 1234567890.0001
     * - 789.000100001233 = 789.00010000123
     * - 789.000100001236 = 789.00010000124
     * </code>
     * @param $value
     * @param $typeErrorMsg
     * @return FunctionResponse
     */
    private function validateDecimal($value, $typeErrorMsg = ValidatorHandler::VALUE_TYPE_IS_NOT_DECIMAL)
    {
        $fRes = new FunctionResponse();

        $res = VarHandler::isFloat($value, $this->strict);

        return ($res) ? $fRes->ok() : $fRes->error($typeErrorMsg, VarHandler::getType($value));
    }

    /**
     * @param $value
     * @return FunctionResponse
     */
    private function validateMaxDecimals($value)
    {
        $fRes = new FunctionResponse();

        if ($this->maxDecimals === null || VarHandler::isFloat($value, false) === false)
            return $fRes->ok();

        if (VarHandler::isString($value) === false)
            $value = (string)$value;

        $parts = StringHandler::explode($value, '.');

        if (count($parts) === 1)
            return $fRes->ok();

        $trimZeros = rtrim($parts[1], '0');

        $res = (strlen($trimZeros) <= $this->maxDecimals);

        if ($res === false)
            return $fRes->error(ValidatorHandler::TOO_MANY_DECIMAL_DIGITS, $this->maxDecimals);

        return $fRes->ok();
    }

    /**
     * Validate Float (alias for validateDecimal)
     * @param $value
     *
     * @return FunctionResponse
     *
     * @see validateDecimal
     */
    private function validateFloat($value)
    {
        return $this->validateDecimal($value, ValidatorHandler::VALUE_TYPE_IS_NOT_FLOAT);
    }

    private function validateNegative($value)
    {
        $fRes = new FunctionResponse();

        if ($this->negative === null)
            return $fRes->ok();

        $res = (StringHandler::substr((float)$value . "", 0, 1) === '-');

        return ($res) ? $fRes->ok() : $fRes->error(ValidatorHandler::VALUE_IS_NOT_NEGATIVE);
    }

    private function validatePositive($value)
    {
        $fRes = new FunctionResponse();

        if ($this->positive === null)
            return $fRes->ok();

        $res = (StringHandler::has((float)$value . "", '-') === false);

        return ($res) ? $fRes->ok() : $fRes->error(ValidatorHandler::VALUE_IS_NOT_POSITIVE);
    }

    private function validateMinimum($value)
    {
        $fRes = new FunctionResponse();

        if ($this->min === null || VarHandler::isNumeric($value) === false)
            return $fRes->ok();

        $res = (float)$value >= $this->min;

        return ($res) ? $fRes->ok() : $fRes->error(ValidatorHandler::VALUE_IS_LESS_THAN_MINIMUM, $this->min);
    }

    private function validateMaximum($value)
    {
        $fRes = new FunctionResponse();

        if ($this->max === null || VarHandler::isNumeric($value) === false)
            return $fRes->ok();

        $res = (float)$value <= $this->max;

        return ($res) ? $fRes->ok() : $fRes->error(ValidatorHandler::VALUE_IS_GREATER_THAN_MAXIMUM, $this->max);
    }

    private function validateSplitDate($params)
    {
        $fields = $this->splitDateList;

        $year = ArrayHandler::hasKey($params, $fields[0]) ? $params[$fields[0]] : null;
        $month = ArrayHandler::hasKey($params, $fields[1]) ? $params[$fields[1]] : null;
        $day = ArrayHandler::hasKey($params, $fields[2]) ? $params[$fields[2]] : null;

        if (StringHandler::isEmpty($day) || StringHandler::isEmpty($month) || StringHandler::isEmpty($year))
            return false;

        return DateHandler::isValidSplitDate($year, $month, $day);
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
     * @return FunctionResponse
     */
    private function validateAlpha($value)
    {
        $fRes = new FunctionResponse();

        $extraCharacters = Kernel::getValidator()->config->alpha_non_strict_extra_characters;

        $extraCharacters = $extraCharacters . $this->alphaExtraCharacters;

        if ($this->strict)
            $extraCharacters = null;

        $res = VarHandler::isAlpha($value, $this->alphaAllowSpaces, $extraCharacters);

        return ($res) ? $fRes->ok() : $fRes->error(ValidatorHandler::VALUE_TYPE_IS_NOT_ALPHABETIC, [
            'allow_spaces' => $this->alphaAllowSpaces,
            'allow_chars' => $extraCharacters
        ]);
    }

    /**
     * @param $value
     * @return FunctionResponse
     */
    private function validateAlphaNumeric($value)
    {
        $fRes = new FunctionResponse();

        $extraCharacters = Kernel::getValidator()->config->alpha_non_strict_extra_characters;

        if ($this->strict)
            $extraCharacters = null;

        $res = VarHandler::isAlphaNumeric($value, $this->alphaAllowSpaces, $extraCharacters);

        return ($res) ? $fRes->ok() : $fRes->error(ValidatorHandler::VALUE_TYPE_IS_NOT_ALPHABETIC_OR_NUMERIC, [
            'allow_spaces' => $this->alphaAllowSpaces,
            'allow_chars' => ($extraCharacters === null) ? '' : $extraCharacters
        ]);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateEnum($value)
    {
        if ($this->enumList === null)
            return false;

        return ArrayHandler::hasValue($this->enumList, $value, $this->strict);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateDate($value)
    {
        return DateHandler::isDateValid($value, $this->dateFormat);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateTime($value)
    {
        return DateHandler::isTimeValid($value, $this->timeFormat);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateDateTime($value)
    {
        return DateHandler::isDateTimeValid($value, $this->dateTimeFormat);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateYear($value)
    {
        return VarHandler::isInt($value, false);
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

        // empty and not required

        if ($this->required === false && $this->validateValueRequired($value) === false
            && $this->type !== self::SPLIT_DATE)
            return $res->ok();

        // required

        if ($this->required === true && $this->validateValueRequired($value) === false)
            return $res->error(ValidatorHandler::VALUE_CANNOT_BE_EMPTY);

        // match

        if ($this->match !== null && (ArrayHandler::hasKey($params, $this->match) === false || $params[$this->match] !== $value))
            return $res->error(ValidatorHandler::VALUE_DOES_NOT_MATCH, $this->match);

        // max decimals

        $maxDecimalsValidationRes = $this->validateMaxDecimals($value);
        if ($maxDecimalsValidationRes->hasError())
            return $res->error($maxDecimalsValidationRes->msg, $maxDecimalsValidationRes->result);

        // length

        $lengthValidationRes = $this->validateValueLength($value);
        if ($lengthValidationRes->hasError())
            return $res->error($lengthValidationRes->msg, $lengthValidationRes->result);

        // regex

        $regexValidationRes = $this->validateValueRegex($value);
        if ($regexValidationRes->hasError())
            return $res->error($regexValidationRes->msg, $regexValidationRes->result);

        // positive

        $posValidationRes = $this->validatePositive($value);
        if ($posValidationRes->hasError())
            return $res->error($posValidationRes->msg);

        // negative

        $negValidationRes = $this->validateNegative($value);
        if ($negValidationRes->hasError())
            return $res->error($negValidationRes->msg);

        // maximum

        $maxValidationRes = $this->validateMaximum($value);
        if ($maxValidationRes->hasError())
            return $res->error($maxValidationRes->msg, $maxValidationRes->result);

        // minimum

        $minValidationRes = $this->validateMinimum($value);
        if ($minValidationRes->hasError())
            return $res->error($minValidationRes->msg, $minValidationRes->result);

        // type

        /** @var bool|null $typeValidationStatus */
        $typeValidationStatus = null;

        switch ($this->type) {
            case self::STRING:
                if ($this->validateString($value) === false)
                    return $res->error(ValidatorHandler::VALUE_TYPE_IS_NOT_STRING, VarHandler::getType($value));
                break;
            case self::INTEGER:
                if ($this->validateInteger($value) === false)
                    return $res->error(ValidatorHandler::VALUE_TYPE_IS_NOT_INTEGER, VarHandler::getType($value));
                break;
            case self::BOOLEAN:
                if ($this->validateBoolean($value) === false)
                    return $res->error(ValidatorHandler::VALUE_TYPE_IS_NOT_BOOLEAN, VarHandler::getType($value));
                break;
            case self::FLOAT:
                $checkRes = $this->validateFloat($value);
                if ($checkRes->hasError())
                    return $checkRes;
                break;
            case self::DECIMAL:
                $checkRes = $this->validateDecimal($value);
                if ($checkRes->hasError())
                    return $checkRes;
                break;
            case self::ENUM:
                if ($this->validateEnum($value) === false)
                    return $res->error(ValidatorHandler::WRONG_ENUM_VALUE);
                break;
            case self::DATE:
                if ($this->validateDate($value) === false)
                    return $res->error(ValidatorHandler::INVALID_DATE_FORMAT);
                break;
            case self::SPLIT_DATE:
                if ($this->validateSplitDate($params) === false)
                    return $res->error(ValidatorHandler::INVALID_DATE_FORMAT);
                break;
            case self::TIME:
                if ($this->validateTime($value) === false)
                    return $res->error(ValidatorHandler::INVALID_TIME_FORMAT);
                break;
            case self::DATETIME:
                if ($this->validateDateTime($value) === false)
                    return $res->error(ValidatorHandler::INVALID_DATETIME_FORMAT);
                break;
            case self::YEAR:
                if ($this->validateYear($value) === false)
                    return $res->error(ValidatorHandler::INVALID_YEAR_FORMAT);
                break;
            case self::NUMERIC:
                if ($this->validateNumeric($value) === false)
                    return $res->error(ValidatorHandler::VALUE_TYPE_IS_NOT_NUMERIC);
                break;
            case self::ALPHA:
                $checkRes = $this->validateAlpha($value);
                if ($checkRes->hasError())
                    return $checkRes;
                break;
            case self::ALPHA_NUMERIC:
                $checkRes = $this->validateAlphaNumeric($value);
                if ($checkRes->hasError())
                    return $checkRes;
                break;
            case self::EMAIL:
            case self::PHONE:
                // this types are processed on creation (mostly they have regex validation, min/max length)
                return $res->ok();
            default:
                return $res->error(ValidatorHandler::WRONG_VALIDATION_TYPE);
        }

        return $res->ok();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}