<?php


namespace Copper\Component\Validator;


use Copper\Component\DB\DBConfigurator;
use Copper\Component\DB\DBModel;

class ValidatorHandler
{
    private $rules;

    /** @var ValidatorConfigurator */
    public $config;
    /** @var DBConfigurator */
    public $dbConfig;

    /**
     * ValidatorHandler constructor.
     *
     * @param DBConfigurator $dbConfig
     * @param ValidatorConfigurator $packageConfig
     * @param ValidatorConfigurator $projectConfig
     */
    public function __construct(DBConfigurator $dbConfig, ValidatorConfigurator $packageConfig, ValidatorConfigurator $projectConfig = null)
    {
        $this->dbConfig = $dbConfig;
        $this->config = $this->mergeConfig($packageConfig, $projectConfig);
        $this->rules = [];
    }

    private function mergeConfig(ValidatorConfigurator $packageConfig, ValidatorConfigurator $projectConfig = null)
    {
        if ($projectConfig === null)
            return $packageConfig;

        $vars = get_object_vars($projectConfig);

        foreach ($vars as $key => $value) {
            if ($value !== null || trim($value) !== "")
                $packageConfig->$key = $value;
        }

        return $packageConfig;
    }

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

            elseif ($field->typeIsFloat())
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
    }

    public function validate()
    {

    }
}