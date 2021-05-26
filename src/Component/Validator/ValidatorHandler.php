<?php


namespace Copper\Component\Validator;


use Copper\Component\DB\DBModel;
use Copper\FunctionResponse;
use Copper\Handler\VarHandler;
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
    public function __construct(string $configFilename)
    {
        $this->config = $this->configure(ValidatorConfigurator::class, $configFilename);

        $this->rules = [];
    }

    /**
     * @param ValidatorRule $rule
     */
    public function addRule(ValidatorRule $rule)
    {
        $this->rules[] = $rule;
    }

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
                $rule = ValidatorRule::integer($name, $field->getLength(), ($field->getNull() !== false));

            else if ($field->typeIsFloat())
                $rule = ValidatorRule::float($name);

            else if ($field->typeIsBoolean())
                $rule = ValidatorRule::boolean($name);

            else if ($field->typeIsEnum())
                $rule = ValidatorRule::enum($name, $field->getLength());

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
     * @param string $lang
     * @param string $textClass
     *
     * @return FunctionResponse
     */
    private function translateErrorRes(FunctionResponse $validationRes, string $lang, string $textClass)
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
            $validationRes = $rule->validate($params, $rule->name);

            if ($validationRes->hasError())
                $errors[$rule->name] = $this->translateErrorRes($validationRes, $lang, $textClass);
        }

        return $response->successOrError(count($errors) === 0, $errors);
    }
}