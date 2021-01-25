<?php


namespace Copper\Component\CP\DB;


use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBModelField;
use Copper\FunctionResponse;
use Copper\Kernel;

class DBGenerator
{
    const T = '    ';
    const T2 = self::T . self::T;

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
        $model = $content['model'] ?? false;
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

        $responses['entity'] = self::createEntity($create_entity, $entity, $fields, $use_state_fields, $entity_override);
        $responses['model'] = self::createModel($table, $create_model, $model, $fields, $use_state_fields, $model_override);
        $responses['service'] = self::createService($create_service, $model, $entity, $service, $service_override);
        $responses['seed'] = self::createSeed($create_seed, $model, $entity, $seed, $seed_override);
        $responses['controller'] = self::createController($create_controller, $model, $entity, $service, $controller, $controller_override);

        return $response->ok('success', $responses);
    }

    private static function filePath($name, $type)
    {
        $folder = Kernel::getProjectPath() . '/src/' . $type;

        if (file_exists($folder) === false)
            mkdir($folder);

        return $folder . '/' . $name . '.php';
    }

    private static function isTypeInteger($type)
    {
        return DBModel::fieldTypeIsInteger($type);
    }

    private static function isTypeFloat($type)
    {
        return DBModel::fieldTypeIsFloat($type);
    }

    private static function createController($create, $model, $entity, $service, $name, $override)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Controller');

        if ($create === false)
            return $response->ok('Skipped');

        if (file_exists($filePath) && $override === false)
            return $response->fail($name . ' is not created. Override is set to false.');

        $template = strtolower($entity);

        $content = <<<XML
<?php

namespace App\Controller;

use App\Model\\$model;
use App\Service\\$service;
use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBOrder;
use Copper\Controller\AbstractController;

class $name extends AbstractController
{
    /** @var DBModel */
    public \$model;

    public function init()
    {
        \$this->model = new $model();
    }

    public function getList()
    {
        \$limit = \$this->request->query->get('limit', 20);
        \$offset = \$this->request->query->get('offset', 0);
        \$order = \$this->request->query->get('order', DBOrder::ASC);
        \$order_by = \$this->request->query->get('order_by', DBModel::ID);

        \$dbOrder = new DBOrder(\$this->model, \$order_by, (\$order === DBOrder::ASC));

        \$list = $service::getList(\$this->db, \$limit, \$offset, \$dbOrder);

        return \$this->render('$template/list', ['list' => \$list]);
    }

}
XML;
        file_put_contents($filePath, $content);

        return $response->ok();
    }

    private static function createSeed($create, $model, $entity, $name, $override)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Seed');

        if ($create === false)
            return $response->ok('Skipped');

        if (file_exists($filePath) && $override === false)
            return $response->fail($name . ' is not created. Override is set to false.');

        $var = strtolower($entity);

        $content = <<<XML
<?php

namespace App\Seed;

use App\Entity\\$entity;
use App\Model\\$model;
use Copper\Component\DB\DBSeed;

class $name extends DBSeed
{
    public function getModelClassName()
    {
        return $model::class;
    }

    public function setSeeds()
    {
        // $$var = new $entity();
        // $${var}->enabled = true;
        // \$this->seed(\$$var);
    }
}
XML;
        file_put_contents($filePath, $content);

        return $response->ok();

    }

    private static function createService($create, $model, $entity, $name, $override)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Service');

        if ($create === false)
            return $response->ok('Skipped');

        if (file_exists($filePath) && $override === false)
            return $response->fail($name . ' is not created. Override is set to false.');

        $useEntity = 'use App\Entity\\' . $entity;
        $useModel = 'use App\Model\\' . $model;

        $content = <<<XML
<?php


namespace App\Service;

$useEntity;
$useModel;
use Copper\Component\DB\DBCollectionService;

class $name extends DBCollectionService
{
    public static function getModelClassName()
    {
        return $model::class;
    }

    public static function getEntityClassName()
    {
        return $entity::class;
    }

}
XML;
        file_put_contents($filePath, $content);

        return $response->ok();
    }

    private static function createModel($table, $create, $name, $fields, $use_state_fields, $override)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Model');

        if ($create === false)
            return $response->ok('Skipped');

        if (file_exists($filePath) && $override === false)
            return $response->fail($name . ' is not created. Override is set to false.');

        $constFields = '';
        $fieldSet = '';
        $stateFieldsFunc = ($use_state_fields)
            ? self::T2 . '// ------ State Fields ------' . "\r\n" . self::T2 . '$this->addStateFields();' : '';

        foreach ($fields as $field) {
            $fName = $field['name'];
            $fType = $field['type'];
            $fLength = $field['length'];
            $fDefault = $field['default'];
            $fAttr = $field['attr'];
            $fNull = $field['null'];
            $fIndex = $field['index'];
            $fAutoIncrement = $field['auto_increment'];

            $fNameUp = strtoupper($fName);

            $constFields .= self::T . "const $fNameUp = '$fName';\r\n";

            $fieldSetStr = self::T2 . '$this->' . "addField(self::$fNameUp, DBModelField::$fType";

            if (in_array($fType, [DBModelField::DECIMAL, DBModelField::ENUM]) !== false) {
                $q = ($fType === DBModelField::DECIMAL) ? '' : "'";
                $fLength = "[$q" . join("$q, $q", explode(',', $fLength)) . "$q]";
            }

            $fieldSetStr .= ($fLength !== false) ? ', ' . $fLength . ')' : ')';

            // if (strtolower($fName) === 'id' && $fIndex === DBModelField::INDEX_PRIMARY && strpos($fType, 'INT') !== false)
            //    $fAutoIncrement = false;

            if ($fIndex === DBModelField::INDEX_PRIMARY)
                $fieldSetStr .= '->primary()';
            elseif ($fIndex === DBModelField::INDEX_UNIQUE)
                $fieldSetStr .= '->unique()';

            if ($fAttr === DBModelField::ATTR_UNSIGNED)
                $fieldSetStr .= '->unsigned()';
            elseif ($fAttr === DBModelField::ATTR_BINARY)
                $fieldSetStr .= '->binary()';
            elseif ($fAttr === DBModelField::ATTR_UNSIGNED_ZEROFILL)
                $fieldSetStr .= '->unsignedZeroFill()';
            elseif ($fAttr === DBModelField::ATTR_ON_UPDATE_CURRENT_TIMESTAMP)
                $fieldSetStr .= '->currentTimestampOnUpdate()';

            if ($fAutoIncrement === true)
                $fieldSetStr .= '->autoIncrement()';

            if ($fDefault === DBModelField::DEFAULT_NULL)
                $fieldSetStr .= '->nullByDefault()';
            elseif ($fDefault === DBModelField::DEFAULT_CURRENT_TIMESTAMP)
                $fieldSetStr .= '->currentTimestampByDefault()';
            elseif ($fDefault !== DBModelField::DEFAULT_NONE) {
                if (self::isTypeFloat($fType) === false && self::isTypeInteger($fType) === false)
                    $fDefault = "'$fDefault'";

                $fieldSetStr .= "->default($fDefault)";
            }

            if ($fNull && $fDefault !== DBModelField::DEFAULT_NULL)
                $fieldSetStr .= "->null()";

            $fieldSet .= $fieldSetStr . ";\r\n";
        }

        $content = <<<XML
<?php

namespace App\Model;

use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBModelField;

class $name extends DBModel
{
$constFields
    public function getTableName()
    {
        return '$table';
    }

    public function setFields()
    {
$fieldSet
$stateFieldsFunc
    }

}
XML;

        file_put_contents($filePath, $content);

        return $response->ok();
    }

    private static function createEntity($create, $name, $fields, $use_state_fields, $override)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Entity');

        if ($create === false)
            return $response->ok('Skipped');

        if (file_exists($filePath) && $override === false)
            return $response->fail($name . ' is not created. Override is set to false.');

        $use_state_fields_trait = ($use_state_fields === true) ? "use EntityStateFields;\r\n" : "\r\n";
        $use_state_fields_trait_class = ($use_state_fields === true) ? "use Copper\Traits\EntityStateFields;\r\n" : "\r\n";

        $fields_content = '';

        foreach ($fields as $field) {
            $fName = $field['name'];
            $type = 'string';

            if (self::isTypeInteger($field['type']))
                $type = 'integer';

            if (self::isTypeFloat($field['type']))
                $type = 'float';

            if ($field['type'] === DBModelField::BOOLEAN)
                $type = 'boolean';

            $fields_content .= "    /** @var $type */\r\n    public $$fName;\r\n";
        }

        $content = <<<XML
<?php


namespace App\Entity;


use Copper\Entity\AbstractEntity;
$use_state_fields_trait_class
class $name extends AbstractEntity
{
    $use_state_fields_trait
$fields_content
}
XML;

        file_put_contents($filePath, $content);

        return $response->ok();
    }
}