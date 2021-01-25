<?php


namespace Copper\Component\Validator;


use Copper\Component\DB\DBModel;

class ValidatorHandler
{
    private $rules;

    public function addRule(ValidatorRule $rule)
    {

    }

    public static function validateModel($params, DBModel $model)
    {
        $validator = new self();

        foreach ($model->getFieldNames() as $name) {
            $field = $model->getFieldByName($name);

            if (DBModel::fieldTypeIsInteger($field->type))
                $rule = ValidatorRule::integer($name);
            elseif (DBModel::fieldTypeIsFloat($field->type))
                $rule = ValidatorRule::float($name);
            else if (DBModel::fieldTypeIsBoolean($field->type))
                $rule = ValidatorRule::boolean($name);
            else if (DBModel::fieldTypeIsEnum($field->type))
                $rule = ValidatorRule::enum($name, $field->length);
            else if (DBModel::fieldTypeIsDecimal($field->type))
                $rule = ValidatorRule::decimal($name, $field->length[0], $field->length[1]);
            else
                $rule = ValidatorRule::string($name);

            if ($field->null === false)
                $rule->required();

            $validator->addRule($rule);
        }
    }

    public function validate()
    {

    }
}