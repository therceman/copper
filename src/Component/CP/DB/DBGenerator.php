<?php


namespace Copper\Component\CP\DB;


use Copper\Component\DB\DBModelField;
use Copper\FunctionResponse;
use Copper\Kernel;

class DBGenerator
{
    /**
     * @param $jsonContent
     * @return FunctionResponse
     */
    public static function run($jsonContent)
    {
        $response = new FunctionResponse();

        $content = json_decode($jsonContent, true);

        $table = $content['table'] ?? false;
        $entity = $content['entity'] ?? false;
        $service = $content['service'] ?? false;
        $seed = $content['seed'] ?? false;
        $controller = $content['controller'] ?? false;

        $fields = $content['fields'] ?? false;

        $use_state_fields = $content['use_state_fields'] ?? false;

        $model_override = $content['model_override'] ?? false;
        $entity_override = $content['entity_override'] ?? false;
        $service_override = $content['service_override'] ?? false;
        $seed_override = $content['seed_override'] ?? false;
        $controller_override = $content['controller_override'] ?? false;

        $create_entity = $content['create_entity'] ?? false;
        $create_model = $content['create_model'] ?? false;
        $create_service = $content['create_service'] ?? false;
        $create_seed = $content['create_seed'] ?? false;
        $create_controller = $content['create_controller'] ?? false;

        if ($table === false || $fields === false)
            return $response->fail('Please provide all information. Table, Fields');

        $responses = [];

        if ($create_entity === true)
            $responses['entity'] = self::createEntity($entity, $fields, $use_state_fields, $entity_override);
        else
            $responses['entity'] = new FunctionResponse(true, 'Skipped');

        return $response->ok('success', $responses);
    }

    private static function createEntity($entity, $fields, $use_state_fields, $entity_override)
    {
        $response = new FunctionResponse();

        $file = Kernel::getProjectPath() . '/src/Entity/' . $entity . '.php';

        $use_state_fields_trait = ($use_state_fields === true) ? "use EntityStateFields;\r\n" : "\r\n";
        $use_state_fields_trait_class = ($use_state_fields === true) ? "use Copper\Traits\EntityStateFields;\r\n" : "\r\n";

        $fields_content = '';

        foreach ($fields as $field) {
            $name = $field['name'];
            $type = 'string';

            if (in_array($field['type'], [
                    DBModelField::INT,
                    DBModelField::TINYINT,
                    DBModelField::SMALLINT,
                    DBModelField::MEDIUMINT,
                    DBModelField::BIGINT,
                    DBModelField::SERIAL,
                    DBModelField::BIT
                ]) !== false)
                $type = 'integer';

            if (in_array($field['type'], [
                    DBModelField::DECIMAL,
                    DBModelField::FLOAT,
                    DBModelField::DOUBLE,
                    DBModelField::REAL
                ]) !== false)
                $type = 'float';

            if ($field['type'] === DBModelField::BOOLEAN)
                $type = 'boolean';

            $fields_content .= "    /** @var $type */\r\n    public $$name;\r\n";
        }

        $content = <<<XML
<?php


namespace App\Entity;


use Copper\Entity\AbstractEntity;
$use_state_fields_trait_class
class $entity extends AbstractEntity
{
    $use_state_fields_trait
$fields_content
}
XML;

        if (file_exists($file) && $entity_override === false)
            return $response->fail('Entity not created. Override is set to false.');

        file_put_contents($file, $content);

        return $response->ok();
    }
}