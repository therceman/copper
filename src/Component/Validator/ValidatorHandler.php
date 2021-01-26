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

            if (DBModel::fieldTypeIsInteger($field->type))
                $rule = ValidatorRule::integer($name, $field->length, ($field->null !== false));

            elseif (DBModel::fieldTypeIsFloat($field->type))
                $rule = ValidatorRule::float($name);

            else if (DBModel::fieldTypeIsBoolean($field->type))
                $rule = ValidatorRule::boolean($name);

            else if (DBModel::fieldTypeIsEnum($field->type))
                $rule = ValidatorRule::enum($name, $field->length);

            else if (DBModel::fieldTypeIsDecimal($field->type))
                $rule = ValidatorRule::decimal($name, $field->length[0], $field->length[1]);

            else if (DBModel::fieldTypeIsDate($field->type))
                $rule = ValidatorRule::date($name, ($field->null !== false));

            else if (DBModel::fieldTypeIsTime($field->type))
                $rule = ValidatorRule::time($name);

            else if (DBModel::fieldTypeIsDatetime($field->type))
                $rule = ValidatorRule::datetime($name);

            else if (DBModel::fieldTypeIsYear($field->type))
                $rule = ValidatorRule::year($name);

            else
                $rule = ValidatorRule::string($name);

            if ($field->null === false)
                $rule->required();

            $this->addRule($rule);
        }
    }

    public function validate()
    {

    }
}