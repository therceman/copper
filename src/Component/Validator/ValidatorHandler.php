<?php


namespace Copper\Component\Validator;


use Copper\Component\DB\DBModel;
use Copper\FunctionResponse;
use Copper\Handler\VarHandler;
use Copper\Kernel;
use Copper\Traits\ComponentHandlerTrait;

class ValidatorHandler
{
    use ComponentHandlerTrait;

    /** @var ValidatorRule[] */
    private $rules;

    /** @var ValidatorConfigurator */
    public $config;

    /**
     * ValidatorHandler constructor.
     *
     * @param string $configFilename
     */
    public function __construct(string $configFilename = Kernel::VALIDATOR_CONFIG_FILE)
    {
        $this->config = $this->configure(ValidatorConfigurator::class, $configFilename);

        $this->rules = [];
    }

    /**
     * @param ValidatorRule $rule
     * @return ValidatorRule
     */
    public function addRule(ValidatorRule $rule)
    {
        $this->rules[] = $rule;

        return $rule;
    }

    // -------------------- Shortcuts --------------------

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addStringRule(string $name, $required = false)
    {
        return $this->addRule(ValidatorRule::string($name, $required));
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public function addIntegerRule(string $name, $required = false, $strict = false)
    {
        return $this->addRule(ValidatorRule::integer($name, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public function addIntegerNegativeRule(string $name, $required = false, $strict = false)
    {
        return $this->addRule(ValidatorRule::integerNegative($name, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public function addIntegerPositiveRule(string $name, $required = false, $strict = false)
    {
        return $this->addRule(ValidatorRule::integerPositive($name, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public function addBooleanRule(string $name, $required = false, $strict = false)
    {
        return $this->addRule(ValidatorRule::boolean($name, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public function addFloatRule(string $name, $required = false, $strict = false)
    {
        return $this->addRule(ValidatorRule::float($name, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public function addFloatNegativeRule(string $name, $required = false, $strict = false)
    {
        return $this->addRule(ValidatorRule::floatNegative($name, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public function addFloatPositiveRule(string $name, $required = false, $strict = false)
    {
        return $this->addRule(ValidatorRule::floatPositive($name, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addEmailRule(string $name, $required = false)
    {
        return $this->addRule(ValidatorRule::email($name, $required));
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addPhoneRule(string $name, $required = false)
    {
        return $this->addRule(ValidatorRule::phone($name, $required));
    }

    /**
     * @param string $name
     * @param array|null $values
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addEnumRule(string $name, $values = null, $required = false)
    {
        return $this->addRule(ValidatorRule::enum($name, $values, $required));
    }

    /**
     * @param string $name
     * @param int $maxDigits
     * @param int $maxDecimals
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addDecimalRule(string $name, int $maxDigits, int $maxDecimals, $required = false)
    {
        return $this->addRule(ValidatorRule::decimal($name, $maxDigits, $maxDecimals, $required));
    }

    /**
     * @param string $name
     * @param int $maxDigits
     * @param int $maxDecimals
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public function addDecimalPositiveRule(string $name, int $maxDigits, int $maxDecimals, $required = false, $strict = false)
    {
        return $this->addRule(ValidatorRule::decimalPositive($name, $maxDigits, $maxDecimals, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param int $maxDigits
     * @param int $maxDecimals
     * @param bool $required
     * @param bool $strict
     *
     * @return ValidatorRule
     */
    public function addDecimalNegativeRule(string $name, int $maxDigits, int $maxDecimals, $required = false, $strict = false)
    {
        return $this->addRule(ValidatorRule::decimalNegative($name, $maxDigits, $maxDecimals, $required))->strict($strict);
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addDateRule(string $name, $required = false)
    {
        return $this->addRule(ValidatorRule::date($name, $required));
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addTimeRule(string $name, $required = false)
    {
        return $this->addRule(ValidatorRule::time($name, $required));
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addDateTimeRule(string $name, $required = false)
    {
        return $this->addRule(ValidatorRule::datetime($name, $required));
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addYearRule(string $name, $required = false)
    {
        return $this->addRule(ValidatorRule::year($name, $required));
    }

    /**
     * @param string $name
     * @param bool $required
     *
     * @return ValidatorRule
     */
    public function addNumericRule(string $name, $required = false)
    {
        return $this->addRule(ValidatorRule::numeric($name, $required));
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $allowSpaces
     *
     * @return ValidatorRule
     */
    public function addAlphaRule(string $name, $required = false, $allowSpaces = true)
    {
        return $this->addRule(ValidatorRule::alpha($name, $required, $allowSpaces));
    }

    /**
     * @param string $name
     * @param bool $required
     * @param bool $allowSpaces
     *
     * @return ValidatorRule
     */
    public function addAlphaNumericRule(string $name, $required = false, $allowSpaces = true)
    {
        return $this->addRule(ValidatorRule::alphaNumeric($name, $required, $allowSpaces));
    }

    // -------- END of shortcuts ----------------

    public function clearRules()
    {
        $this->rules = [];
    }

    public function validateModel($params, DBModel $model)
    {
        foreach ($model->getFieldNames() as $name) {
            $field = $model->getFieldByName($name);

            $length = $field->getMaxLength();

            if ($field->typeIsInteger())
                $rule = ValidatorRule::integer($name, ($field->getNull() !== false))->maxLength($field->getLength());

            else if ($field->typeIsFloat())
                $rule = ValidatorRule::float($name);

            else if ($field->typeIsBoolean())
                $rule = ValidatorRule::boolean($name);

            else if ($field->typeIsEnum())
                $rule = ValidatorRule::enum($name)->maxLength($field->getLength());

            else if ($field->typeIsDecimal())
                $rule = ValidatorRule::decimal($name, $field->getLength()[0], $field->getLength()[1]);

            else if ($field->typeIsDate())
                $rule = ValidatorRule::date($name, ($field->getNull() !== false));

            else if ($field->typeIsTime())
                $rule = ValidatorRule::time($name);

            else if ($field->typeIsDatetime())
                $rule = ValidatorRule::datetime($name);

            else if ($field->typeIsYear())
                $rule = ValidatorRule::year($name);

            else
                $rule = ValidatorRule::string($name);

            if ($field->getNull() === false)
                $rule->required();

            $this->addRule($rule);
        }

        // TODO ... need all rules validation
        return FunctionResponse::createSuccess('ok');
    }

    /**
     * @param FunctionResponse $validationRes
     * @param string|null $lang
     * @param string|null $textClass
     *
     * @return FunctionResponse
     */
    private function translateErrorRes(FunctionResponse $validationRes, ?string $lang, ?string $textClass)
    {
        $result = $validationRes->result;

        $result = VarHandler::isArray($result) ? $result : [$result];

        if ($textClass !== null && method_exists($textClass, $validationRes->msg))
            $validationRes->msg = $textClass::{$validationRes->msg}($lang, $result);

        return $validationRes;
    }

    /**
     * @param array $params
     * @param string $lang
     * @param string|null $textClass
     *
     * @return FunctionResponse
     */
    public function validate(array $params, $lang = 'en', string $textClass = null)
    {
        $response = new FunctionResponse();

        $errors = [];

        foreach ($this->rules as $rule) {
            $validationRes = $rule->validate($params, $rule->getName());

            if ($validationRes->hasError())
                $errors[$rule->getName()] = $this->translateErrorRes($validationRes, $lang, $textClass);
        }

        return $response->successOrError(count($errors) === 0, $errors);
    }
}