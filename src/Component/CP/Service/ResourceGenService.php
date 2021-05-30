<?php


namespace Copper\Component\CP\Service;


use Copper\Component\DB\DBModel;
use Copper\Handler\ArrayHandler;
use Copper\Component\CP\CPController;
use Copper\Component\DB\DBModelField;
use Copper\Component\DB\DBService;
use Copper\Handler\FileHandler;
use Copper\FunctionResponse;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;
use Copper\Kernel;
use Copper\Resource\AbstractResource;
use Copper\Traits\EntityStateFields;
use ReflectionClass;

class ResourceGenService
{
    const T = '    ';
    const T2 = self::T . self::T;

    private static function fieldHasCustomDefaultValue($defaultValue)
    {
        return (in_array($defaultValue, [DBModelField::DEFAULT_NULL, DBModelField::DEFAULT_CURRENT_TIMESTAMP, DBModelField::DEFAULT_NONE]) === false);
    }

    private static function prepare_controller(string $resourceClassName, string $tpl_folder_name)
    {
        $response = new FunctionResponse();

        /** @var AbstractResource $resource */
        $resource = $resourceClassName;

        $controllerPath = $resource::getControllerPath();

        $contentRes = FileHandler::getContent($controllerPath);

        if ($contentRes->hasError())
            return $contentRes;

        $content = $contentRes->result;

        $content = str_replace('const TEMPLATE_LIST = \'collection/list\';',
            'const TEMPLATE_LIST = \'' . $tpl_folder_name . '/list\';', $content);

        $content = str_replace('const TEMPLATE_FORM = \'collection/form\';',
            'const TEMPLATE_FORM = \'' . $tpl_folder_name . '/form\';', $content);

        $contentSaveRes = FileHandler::setContent($controllerPath, $content);

        if ($contentSaveRes->hasError())
            return $contentSaveRes;

        return $response->ok();
    }

    private static function prepare_list_template(string $resourceClassName, string $filepath)
    {
        return self::prepare_form_template($resourceClassName, $filepath);
    }

    private static function prepare_form_template(string $resourceClassName, string $filepath)
    {
        $response = new FunctionResponse();

        /** @var AbstractResource $resource */
        $resource = $resourceClassName;

        $contentRes = FileHandler::getContent($filepath);

        if ($contentRes->hasError())
            return $contentRes;

        $content = $contentRes->result;

        $content = str_replace('Copper\Entity\AbstractEntity', $resource::getEntityClassName(), $content);
        $content = str_replace('Copper\Resource\AbstractResource', $resource::getClassName(), $content);
        $content = str_replace('AbstractEntity', $resource::getEntityName(), $content);
        $content = str_replace('$Resource', '$' . lcfirst($resource::getName()), $content);

        $field_names_str = '$field_names = [' . PHP_EOL;

        foreach ($resource::getModel()->getFieldNames() as $field) {
            if ($field === DBModel::REMOVED_AT)
                continue;

            $fieldNameNormalized = StringHandler::underscoreToCamelCase($field, true);

            $field_names_str = $field_names_str . self::T . "'$field' => '$fieldNameNormalized'," . PHP_EOL;
        }

        $field_names_str .= ']';

        $content = str_replace('// custom $field_names', $field_names_str, $content);

        $contentSaveRes = FileHandler::setContent($filepath, $content);

        if ($contentSaveRes->hasError())
            return $contentSaveRes;

        return $response->ok();
    }

    public static function create_js_source_files(string $resourceClassName, $force = false)
    {
        $response = new FunctionResponse();

        /** @var AbstractResource $resource */
        $resource = $resourceClassName;

        $entityClassName = $resource::getEntityClassName();
        $entityName = $resource::getEntityName();

        $reflection = new ReflectionClass($entityClassName);

        $property_list = $reflection->getProperties();

        $func_property_list = [];
        $annotation_property_list = [];
        foreach ($property_list as $property) {
            $type = null;
            if (preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches))
                $type = $matches[1];

            if ($type === 'integer' || $type === 'float' || $type === 'int')
                $type = 'number';

            if ($type === 'bool')
                $type = 'boolean';

            $annotation_property_list[] = " * @property {{$type}|null} $property->name";
            $func_property_list[] = "   this.$property->name = null;";
        }
        $annotation_property_list = join("\r\n", $annotation_property_list);
        $func_property_list = join("\r\n", $func_property_list);

        $js_source = <<<XML
'use strict';

/**
 * $entityName
 *
 * @constructor
$annotation_property_list
 */

function $entityName() {
$func_property_list
}
XML;
        $src_js_folder = Kernel::getAppPath() . '/src_js';
        if (FileHandler::fileExists($src_js_folder) === false)
            FileHandler::createFolder($src_js_folder);

        $src_js_entity_folder = $src_js_folder . '/Entity';
        if (FileHandler::fileExists($src_js_entity_folder) === false)
            FileHandler::createFolder($src_js_entity_folder);

        $js_file = $src_js_entity_folder . '/' . $entityName . '.js';

        $js_file_save_res = FileHandler::setContent($js_file, $js_source);

        return $response->result($js_file_save_res);
    }

    /**
     * @param string $resourceClassName
     * @param bool $force
     * @return FunctionResponse
     */
    public static function prepare_templates(string $resourceClassName, $force = false)
    {
        $response = new FunctionResponse();

        /** @var AbstractResource $resource */
        $resource = $resourceClassName;

        $result = [];

        $formFilename = 'form.php';
        $listFilename = 'list.php';

        $tpl_folder_path = FileHandler::packagePathFromArray(['templates', 'collection']);
        $form_tpl_path = FileHandler::pathFromArray([$tpl_folder_path, $formFilename]);
        $list_tpl_path = FileHandler::pathFromArray([$tpl_folder_path, $listFilename]);

        $folder_name = $resource::getModel()->getTableName();

        $dest_tpl_folder_path = FileHandler::appPathFromArray(['templates', $folder_name]);
        $dest_form_tpl_path = FileHandler::pathFromArray([$dest_tpl_folder_path, $formFilename]);
        $dest_list_tpl_path = FileHandler::pathFromArray([$dest_tpl_folder_path, $listFilename]);

        if (FileHandler::fileExists($dest_tpl_folder_path) === false)
            FileHandler::createFolder($dest_tpl_folder_path);

        $form_copy_response = new FunctionResponse();
        if ($force === false && FileHandler::fileExists($dest_form_tpl_path))
            $form_copy_response->fail('Form file exists and $force === false');
        else
            $form_copy_response = FileHandler::copyFileToFolder($form_tpl_path, $dest_tpl_folder_path);

        $result['form_copy'] = $form_copy_response;

        if ($form_copy_response->isOK())
            $result['form_prepare'] = self::prepare_form_template($resource, $dest_form_tpl_path);

        $list_copy_response = new FunctionResponse();
        if ($force === false && FileHandler::fileExists($dest_list_tpl_path))
            $list_copy_response->fail('List file exists and $force === false');
        else
            $list_copy_response = FileHandler::copyFileToFolder($list_tpl_path, $dest_tpl_folder_path);

        $result['list_copy'] = $list_copy_response;

        if ($list_copy_response->isOK())
            $result['list_prepare'] = self::prepare_list_template($resource, $dest_list_tpl_path);

        $result['controller_prepare'] = self::prepare_controller($resource, $folder_name);

        return $response->okOrFail((
            $form_copy_response->isOK()
            && $list_copy_response->isOK()
            && $result['form_prepare']->isOK()
            && $result['list_prepare']->isOK()
            && $result['controller_prepare']->isOK()
        ), $result);
    }

    private static function initTraitsFolder()
    {
        $traitsFolder = Kernel::getAppPath() . '/src/Traits';
        if (FileHandler::fileExists($traitsFolder) === false)
            FileHandler::createFolder($traitsFolder);
    }

    public static function addRoute($content)
    {
        $response = new FunctionResponse();


        return $response->result($content);
    }

    public static function delRoute($content)
    {
        $response = new FunctionResponse();

        return $response->result($content);
    }

    public static function updateRouteList($content)
    {
        $response = new FunctionResponse();

        return $response->result($content);
    }

    /**
     * @param $jsonContent
     * @return FunctionResponse
     */
    public static function delete($jsonContent)
    {
        $response = new FunctionResponse();

        $result_list = [];

        $content = json_decode($jsonContent, true);

        /** @var AbstractResource $resource */
        $resource = $content['resource'];

        if ($content['delete_controller'])
            $result_list['delete_controller'] = FileHandler::delete($resource::getControllerPath());

        if ($content['delete_service'])
            $result_list['delete_service'] = FileHandler::delete($resource::getServicePath());

        if ($content['delete_entity'])
            $result_list['delete_entity'] = FileHandler::delete($resource::getEntityPath());

        if ($content['delete_seed'])
            $result_list['delete_seed'] = FileHandler::delete($resource::getEntityPath());

        if ($content['delete_table'])
            $result_list['delete_table'] = $resource::getModel()->doDeleteTable();

        if ($content['delete_model']) {
            $result_list['delete_model'] = FileHandler::delete($resource::getModelPath());
            $result_list['delete_model_trait'] = FileHandler::delete(
                FileHandler::pathFromArray([
                    Kernel::getAppTraitsPath(),
                    'Annotation',
                    $resource::getModelName() . 'AnnotationTrait.php'
                ])
            );
        }

        if ($content['delete_resource'])
            $result_list['delete_resource'] = FileHandler::delete($resource::getPath());

        return $response->result($result_list);
    }

    /**
     * @param $jsonContent
     * @return FunctionResponse
     */
    public static function run($jsonContent)
    {
        $response = new FunctionResponse();

        $content = json_decode($jsonContent, true);

        $resource = $content['resource'] ?? false;
        $table = $content['table'] ?? false;
        $entity = $content['entity'] ?? false;
        $model = $content['model'] ?? false;
        $service = $content['service'] ?? false;
        $seed = $content['seed'] ?? false;
        $controller = $content['controller'] ?? false;

        $fields = self::formatFields(ArrayHandler::clean($content['fields'])) ?? false;

        $use_state_fields = $content['use_state_fields'] ?? false;

        $resource_override = $content['resource_override'] ?? false;
        $model_override = $content['model_override'] ?? false;
        $entity_override = $content['entity_override'] ?? false;
        $service_override = $content['service_override'] ?? false;
        $seed_override = $content['seed_override'] ?? false;
        $controller_override = $content['controller_override'] ?? false;

        $create_resource = $content['create_resource'] ?? false;
        $create_entity = $content['create_entity'] ?? false;
        $create_model = $content['create_model'] ?? false;
        $create_service = $content['create_service'] ?? false;
        $create_seed = $content['create_seed'] ?? false;
        $create_controller = $content['create_controller'] ?? false;

        if ($table === false || $fields === false)
            return $response->fail('Please provide all information. Table, Fields');

        self::initTraitsFolder();

        $responses = [];

        $responses['entity'] = self::createEntity($create_entity, $entity, $fields, $use_state_fields, $entity_override);
        $responses['model'] = self::createModel($table, $entity, $create_model, $model, $fields, $use_state_fields, $model_override);
        $responses['service'] = self::createService($create_service, $model, $entity, $service, $service_override);
        $responses['seed'] = self::createSeed($create_seed, $model, $entity, $seed, $seed_override);
        $responses['controller'] = self::createController($create_controller, $resource, $model, $entity, $service, $controller, $controller_override, $use_state_fields);

        if ($create_seed === false)
            $seed = false;

        if ($create_controller === false)
            $controller = false;

        if ($create_service === false)
            $service = false;

        $responses['resource'] = self::createResource($create_resource, $table, $model, $entity, $service, $controller, $seed, $resource, $resource_override);

        // add new fields to DB
        if ($content['new_fields_db_update'] === true)
            $responses['new_fields_db_update'] = self::addNewFieldsToDB($table, $content['new_fields'], $fields);

        // delete removed fields from DB
        if ($content['removed_fields_db_update'] === true)
            $responses['removed_fields_db_update'] = self::removeFieldsFromDB($table, $content['removed_fields']);

        // update changed fields in DB
        if ($content['updated_fields_db_update'] === true)
            $responses['updated_fields_db_update'] = self::updateFieldsInDB($table, $content['updated_fields'], $fields);

        $responses['return_url'] = Kernel::getRouteUrl(ROUTE_copper_cp_action,
            ['action' => CPController::ACTION_DB_GENERATOR, 'resource' => 'App\\Resource\\' . $resource], true);

        return $response->result($responses);
    }

    private static function getAfterFieldName($fieldName, $fields)
    {
        $afterField = null;

        $prevFieldData = null;
        foreach ($fields as $fieldData) {
            if ($fieldData['name'] === $fieldName)
                $afterField = $prevFieldData['name'];

            $prevFieldData = $fieldData;
        }

        return $afterField;
    }

    private static function removeFieldsFromDB($table, $removeFields)
    {
        $res = new FunctionResponse();

        $results = [];
        $isOK = true;

        foreach ($removeFields as $removeFieldData) {
            $origName = $removeFieldData['orig_name'];
            $queryStatement = "ALTER TABLE `$table` DROP `$origName`";

            $pdoResult = Kernel::getDb()->pdo->query($queryStatement);
            $results[$origName] = ($pdoResult !== false) ? true : Kernel::getDb()->pdo->errorInfo()[2];

            if ($pdoResult === false)
                $isOK = false;
        }

        return $res->okOrFail($isOK, $results);
    }

    private static function updateFieldsInDB($table, $updateFields, $fields)
    {
        $res = new FunctionResponse();

        $results = [];
        $isOK = true;

        foreach ($updateFields as $updateFieldData) {
            $origName = $updateFieldData['orig_name'];
            $newField = DBModelField::fromArray($updateFieldData);
            $newFieldStatement = DBService::prepareFieldStatementForDB($newField);
            $newFieldAfterFieldName = self::getAfterFieldName($newField->getName(), $fields);

            if ($newFieldAfterFieldName !== null)
                $newFieldStatement = $newFieldStatement . " AFTER `$newFieldAfterFieldName`";

            $queryStatement = "ALTER TABLE `$table` CHANGE `$origName` " . $newFieldStatement;

            $pdoResult = Kernel::getDb()->pdo->query($queryStatement);
            $results[$origName] = ($pdoResult !== false) ? true : Kernel::getDb()->pdo->errorInfo()[2];

            if ($pdoResult === false)
                $isOK = false;
        }

        return $res->okOrFail($isOK, $results);
    }

    private static function reorderNewFields($newFields, $fields)
    {
        $reorderedFields = [];

        foreach ($fields as $field) {
            $foundNewField = ArrayHandler::assocFindStrictFirst($newFields, ['name' => $field['name']]);

            if ($foundNewField !== null)
                $reorderedFields[] = $foundNewField;
        }

        return $reorderedFields;
    }

    private static function addNewFieldsToDB($table, $newFields, $fields)
    {
        $res = new FunctionResponse();

        $results = [];
        $isOK = true;

        $newFields = self::reorderNewFields($newFields, $fields);

        foreach ($newFields as $newFieldData) {
            $newField = DBModelField::fromArray($newFieldData);
            $newFieldStatement = DBService::prepareFieldStatementForDB($newField);
            $newFieldAfterFieldName = self::getAfterFieldName($newField->getName(), $fields);

            if ($newFieldAfterFieldName !== null)
                $newFieldStatement = $newFieldStatement . " AFTER `$newFieldAfterFieldName`";

            $queryStatement = "ALTER TABLE `$table` ADD " . $newFieldStatement;

            $pdoResult = Kernel::getDb()->pdo->query($queryStatement);
            $results[$newField->getName()] = ($pdoResult !== false) ? true : Kernel::getDb()->pdo->errorInfo()[2];

            if ($pdoResult === false)
                $isOK = false;
        }

        return $res->okOrFail($isOK, $results);
    }

    private static function formatFields($fields)
    {
        foreach ($fields as $key => $field) {
            foreach ($field as $fKey => $fVal) {
                if (VarHandler::isArray($fVal))
                    $fields[$key][$fKey] = join(',', $fVal);
            }
        }

        return $fields;
    }

    private static function filePath($name, $type)
    {
        $folder = Kernel::getAppPath() . '/src/' . $type;

        if (FileHandler::fileExists($folder) === false)
            mkdir($folder);

        return $folder . '/' . $name . '.php';
    }

    private static function createResource($create, $table, $model, $entity, $service, $controller, $seed, $name, $override)
    {
        $response = new FunctionResponse();

        $pathGroup = $table;

        $filePath = self::filePath($name, 'Resource');

        if ($create === false)
            return $response->ok('Skipped');

        if (FileHandler::fileExists($filePath) && $override === false)
            return $response->fail($name . ' is not created. Override is set to false.');

        // ---------------------

        $seedClass = ($seed !== false) ? "use App\Seed\\$seed;" : '';
        $seedFunc = ($seed !== false) ? "
    static function getSeedClassName()
    {
        return $seed::class;
    }\r\n" : '';

        // ---------------------

        $serviceClass = ($service !== false) ? "use App\Service\\$service;" : '';
        $serviceFunc = ($service !== false) ? "
    static function getServiceClassName()
    {
        return $service::class;
    }\r\n" : '';

        // ---------------------

        $controllerClass = ($controller !== false) ? "use App\Controller\\$controller;" : '';
        $controllerFunc = ($controller !== false) ? "
    static function getControllerClassName()
    {
        return $controller::class;
    }\r\n" : '';

        // ---------------------

        $routes = "const PATH_GROUP = '$pathGroup';

    const GET_LIST = 'getList@/' . self::PATH_GROUP . '/list';
    const GET_EDIT = 'getEdit@/' . self::PATH_GROUP . '/edit/{id}';
    const POST_UPDATE = 'postUpdate@/' . self::PATH_GROUP . '/update/{id}';
    const GET_NEW = 'getNew@/' . self::PATH_GROUP . '/new';
    const POST_CREATE = 'postCreate@/' . self::PATH_GROUP . '/create';
    const POST_REMOVE = 'postRemove@/' . self::PATH_GROUP . '/remove/{id}';
    const POST_UNDO_REMOVE = 'postUndoRemove@/' . self::PATH_GROUP . '/remove/undo/{id}';
    
    // custom route constants
    
    /** @see $controller */
    public static function registerRoutes(RoutingConfigurator \$routes)
    {
        self::addRoute(\$routes, self::GET_LIST);
        self::addRoute(\$routes, self::GET_EDIT);
        self::addRoute(\$routes, self::POST_UPDATE);
        self::addRoute(\$routes, self::GET_NEW);
        self::addRoute(\$routes, self::POST_CREATE);
        self::addRoute(\$routes, self::POST_REMOVE);
        self::addRoute(\$routes, self::POST_UNDO_REMOVE);
        
        // custom route registration
    }";

        $routingConfiguratorClass = 'use Copper\Component\Routing\RoutingConfigurator;';
        if ($controller === false) {
            $routes = '';
            $routingConfiguratorClass = '';
        }

        $seedMethodAnnotation = ($seed !== false) ? '@method static ' . $seed . ' getSeed()' : '';
        $serviceMethodAnnotation = ($service !== false) ? '@method static ' . $service . ' getService()' : '';

        $content = "<?php

namespace App\Resource;

$controllerClass
use App\Entity\\$entity;
use App\Model\\$model;
$seedClass
$serviceClass
use Copper\Resource\AbstractResource;
$routingConfiguratorClass

/**
 * Class $name
 *
 * @method static $model getModel()
 * @method static $entity getEntity()
 * $serviceMethodAnnotation
 * $seedMethodAnnotation
 *
 * @package App\Resource
 */
class $name extends AbstractResource
{
    static function getEntityClassName()
    {
        return $entity::class;
    }
    $controllerFunc

    static function getModelClassName()
    {
        return $model::class;
    }
    $serviceFunc
    $seedFunc
    $routes
}";
        FileHandler::setContent($filePath, $content);

        return $response->ok();
    }

    private static function createController($create, $resource, $model, $entity, $service, $name, $override, $use_state_fields)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Controller');

        if ($create === false)
            return $response->ok('Skipped');

        if (FileHandler::fileExists($filePath) && $override === false)
            return $response->fail($name . ' is not created. Override is set to false.');

        $excluded_param = "[$model::ID]";

        if ($use_state_fields)
            $excluded_param = "[$model::ID, $model::CREATED_AT, $model::UPDATED_AT, $model::REMOVED_AT]";

        $content = "<?php

namespace App\\Controller;

use App\\Entity\\$entity;
use App\\Model\\$model;
use App\\Service\\$service;
use App\\Resource\\$resource;
use Copper\\Controller\\AbstractController;
use Copper\Traits\ResourceControllerActions;

class $name extends AbstractController
{
    use ResourceControllerActions;
    
    const EXCLUDED_UPDATE_PARAMS = $excluded_param;
    const EXCLUDED_CREATE_PARAMS = $excluded_param;

    const TEMPLATE_LIST = 'collection/list';
    const TEMPLATE_FORM = 'collection/form';

    /** @var $resource */
    private \$resource = $resource::class;

    /** @var $service */
    private \$service;
    /** @var $model */
    private \$model;
    /** @var $entity */
    private \$entity;

    public function init()
    {
        \$this->service = \$this->resource::getService();
        \$this->model = \$this->resource::getModel();
        \$this->entity = \$this->resource::getEntity();
    }
    
    /**
     * Return View to see entity list
     */
    public function getList()
    {
        return \$this->getListAction();
    }

    /**
     * Return View to update existing entity
     * @param \$id
     */
    public function getEdit(\$id)
    {
        return \$this->getEditAction(\$id);
    }

    /**
     * Submit Form to update existing entity
     * @param \$id
     */
    public function postUpdate(\$id)
    {
        return \$this->postUpdateAction(\$id);
    }

    /**
     * Return View to create new entity
     */
    public function getNew()
    {
        return \$this->getNewAction();
    }

    /**
     * Submit Form to create new entity
     */
    public function postCreate()
    {
        return \$this->postCreateAction();
    }

    /**
     * Submit Form to remove entity
     * @param \$id
     */
    public function postRemove(\$id)
    {
        return \$this->postRemoveAction(\$id);
    }

    /**
     * Submit From to undo delete of entity 
     * @param \$id
     */
    public function postUndoRemove(\$id)
    {
        return \$this->postUndoRemoveAction(\$id);
    }

}";
        FileHandler::setContent($filePath, $content);

        return $response->ok();
    }

    private static function createSeed($create, $model, $entity, $name, $override)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Seed');

        if ($create === false)
            return $response->ok('Skipped');

        if (FileHandler::fileExists($filePath) && $override === false)
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
        // \$entity = new $entity();
        // \$entity->enabled = true;
        // \$this->seed(\$entity);
    }
}
XML;
        FileHandler::setContent($filePath, $content);

        return $response->ok();

    }

    private static function createService($create, $model, $entity, $name, $override)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Service');

        if ($create === false)
            return $response->ok('Skipped');

        if (FileHandler::fileExists($filePath) && $override === false)
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
        FileHandler::setContent($filePath, $content);

        return $response->ok();
    }

    private static function createModel($table, $entity, $create, $name, $fields, $use_state_fields, $override)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Model');

        if ($create === false)
            return $response->ok('Skipped');

        if (FileHandler::fileExists($filePath) && $override === false)
            return $response->fail($name . ' is not created. Override is set to false.');

        $constFields = '';
        $fieldSet = '';
        $stateFieldsFunc = ($use_state_fields)
            ? self::T2 . '// ------ State Fields ------' . "\r\n" . self::T2 . '$this->addStateFields();' : '';

        foreach ($fields as $fieldData) {
            $fName = $fieldData['name'];
            $fType = $fieldData['type'];
            $fLength = $fieldData['length'];
            $fDefault = $fieldData['default'];
            $fAttr = $fieldData['attr'];
            $fNull = $fieldData['null'];
            $fIndex = $fieldData['index'];
            $fAutoIncrement = $fieldData['auto_increment'];

            $field = new DBModelField($fName, $fType);

            if (in_array($fType, [DBModelField::DECIMAL, DBModelField::ENUM]) !== false) {
                $q = ($fType === DBModelField::DECIMAL) ? '' : "'";
                $fLength = "[$q" . join("$q, $q", explode(',', $fLength)) . "$q]";
            }

            $fNameUp = strtoupper($fName);

            $constFields .= self::T . "const $fNameUp = '$fName';\r\n";

            if ($fType === DBModelField::ENUM) {
                $fieldLengthList = explode(',', $fieldData['length']);

                foreach ($fieldLengthList as $enumKey) {
                    $enumKeyClean = trim($enumKey);
                    $enumKeyUp = strtoupper(StringHandler::transliterate($enumKeyClean, '_'));
                    $constFields .= self::T . "const {$fNameUp}__$enumKeyUp = '$enumKeyClean';\r\n";
                    $fLength = str_replace("'" . $enumKey . "'", "self::{$fNameUp}__$enumKeyUp", $fLength);
                }

                if (ArrayHandler::hasValue($fieldLengthList, $fDefault) === false && self::fieldHasCustomDefaultValue($fDefault))
                    $fDefault = $fieldLengthList[0];

                $fLength = str_replace('[', '[' . PHP_EOL . ' ' . self::T2 . self::T, $fLength);
                $fLength = str_replace(']', PHP_EOL . self::T2 . ']', $fLength);
                $fLength = str_replace(',', ',' . PHP_EOL . self::T2 . self::T, $fLength);

                if (self::fieldHasCustomDefaultValue($fDefault))
                    $fDefault = str_replace($fDefault, 'self::' . strtoupper($fName) . '__' . strtoupper(StringHandler::transliterate($fDefault, '_')), $fDefault);
            }

            $fieldSetStr = self::T2 . '$this->' . "addField(self::$fNameUp, DBModelField::$fType";

            $fieldSetStr .= ($fLength !== false) ? ', ' . $fLength . ')' : ')';

            // if (strtolower($fName) === 'id' && $fIndex === DBModelField::INDEX_PRIMARY && strpos($fType, 'INT') !== false)
            //    $fAutoIncrement = false;

            if ($fIndex === DBModelField::INDEX_PRIMARY)
                $fieldSetStr .= '->primary()';
            elseif ($fIndex === DBModelField::INDEX_UNIQUE)
                $fieldSetStr .= '->unique()';

            if ($fAttr === DBModelField::ATTR_UNSIGNED)
                $fieldSetStr .= '->unsigned()';
//            elseif ($fAttr === DBModelField::ATTR_BINARY)
//                $fieldSetStr .= '->binary()';
//            elseif ($fAttr === DBModelField::ATTR_UNSIGNED_ZEROFILL)
//                $fieldSetStr .= '->unsignedZeroFill()';
            elseif ($fAttr === DBModelField::ATTR_ON_UPDATE_CURRENT_TIMESTAMP)
                $fieldSetStr .= '->currentTimestampOnUpdate()';

            if ($fAutoIncrement === true)
                $fieldSetStr .= '->autoIncrement()';

            if ($fDefault === DBModelField::DEFAULT_NULL)
                $fieldSetStr .= '->nullByDefault()';
            elseif ($fDefault === DBModelField::DEFAULT_CURRENT_TIMESTAMP)
                $fieldSetStr .= '->currentTimestampByDefault()';
            elseif ($fDefault !== DBModelField::DEFAULT_NONE) {
                if ($field->typeIsFloat() === false && $field->typeIsInteger() === false && $field->typeIsEnum() === false)
                    $fDefault = "'$fDefault'";

                $fieldSetStr .= "->default($fDefault)";
            }

            if ($fNull && $fDefault !== DBModelField::DEFAULT_NULL)
                $fieldSetStr .= "->null()";

            $fieldSet .= $fieldSetStr . ";\r\n";
        }

        $annotationTraitName = $name . 'AnnotationTrait';
        $annotationTraitFilePath = self::filePath($annotationTraitName, 'Traits/Annotation');
        $annotationTraitFileContent = <<<XML
<?php

namespace App\Traits\Annotation;

use App\Entity\\$entity;
use Copper\Component\DB\DBSelect;
use Copper\Component\DB\DBWhere;
use Copper\FunctionResponse;
use Copper\Traits\ModelAnnotationTrait;

/**
 * Trait $annotationTraitName
 * @package App\Traits\Annotation
 */
trait $annotationTraitName
{
    use ModelAnnotationTrait;

    /** @return $entity|null 
     * @see \Copper\Component\DB\DBModel::doSelectFirst() */
    public function doSelectFirst(DBSelect \$select)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @return $entity|null 
     * @see \Copper\Component\DB\DBModel::doSelectFirstWhere() */
    public function doSelectFirstWhere(DBWhere \$where, DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @return $entity|null 
     * @see \Copper\Component\DB\DBModel::doSelectFirstWhereIs() */
    public function doSelectFirstWhereIs(string \$field, \$value, DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @return $entity|null 
     * @see \Copper\Component\DB\DBModel::doSelectById() */
    public function doSelectById(\$id, DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }
    
    /** @return {$entity}[] 
     * @see \Copper\Component\DB\DBModel::doSelectByValueList() */
    public function doSelectByValueList(array \$collection, string \$field, DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }
    
    /** @return {$entity}[] 
     * @see \Copper\Component\DB\DBModel::doSelectByIdList() */
    public function doSelectByIdList(array \$idList, DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @return {$entity}[] 
     * @see \Copper\Component\DB\DBModel::doSelectWhereIs() */
    public function doSelectWhereIs(string \$field, \$value, DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @return {$entity}[] 
     * @see \Copper\Component\DB\DBModel::doSelectWhere() */
    public function doSelectWhere(DBWhere \$where, DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @return {$entity}[] 
     * @see \Copper\Component\DB\DBModel::doSelectUnique() */
    public function doSelectUnique(string \$column, DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @return {$entity}[] 
     * @see \Copper\Component\DB\DBModel::doSelectLimit() */
    public function doSelectLimit(int \$limit, \$offset = 0, DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @return {$entity}[] 
     * @see \Copper\Component\DB\DBModel::doSelect() */
    public function doSelect(DBSelect \$select = null)
    {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @param {$entity}[] \$entityList 
     * @see \Copper\Component\DB\DBModel::doBulkInsert() */
    public function doBulkInsert(array \$entityList) {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @param $entity \$entity 
     * @return FunctionResponse 
     * @see \Copper\Component\DB\DBModel::doInsert() */
    public function doInsert(\$entity) {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }
    
    /** @return $entity|null 
     * @see \Copper\Component\DB\DBModel::getEntity() */
    public function getEntity() {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }

    /** @param $entity \$entity 
     * @see \Copper\Component\DB\DBModel::getFieldValuesFromEntity() */
    public function getFieldValuesFromEntity(\$entity, \$onlySelectedFields = false) {
        return \$this->cpm(__FUNCTION__, func_get_args());
    }
    
}
XML;

        $content = <<<XML
<?php

namespace App\Model;

use App\Entity\\$entity;
use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBModelField;
use App\Traits\Annotation\\$annotationTraitName;

/**
 * Class $name
 * @package App\Model
 */
class $name extends DBModel
{
    use $annotationTraitName;
    
$constFields
    public function getTableName()
    {
        return '$table';
    }
    
    public function getEntityClassName()
    {
        return $entity::class;
    }

    public function setFields()
    {
$fieldSet
$stateFieldsFunc
    }

}
XML;

        FileHandler::setContent($annotationTraitFilePath, $annotationTraitFileContent);
        FileHandler::setContent($filePath, $content);

        return $response->ok();
    }

    private static function createEntity($create, $name, $fields, $use_state_fields, $override)
    {
        $response = new FunctionResponse();

        $filePath = self::filePath($name, 'Entity');

        if ($create === false)
            return $response->ok('Skipped');

        if (FileHandler::fileExists($filePath) && $override === false)
            return $response->fail($name . ' is not created. Override is set to false.');

        $use_state_fields_trait = ($use_state_fields === true) ? "use EntityStateFields;" : "";
        $use_state_fields_trait_class = ($use_state_fields === true) ? "use Copper\Traits\EntityStateFields;" : "";

        $fields_content = '';

        foreach ($fields as $field) {
            $fName = $field['name'];
            $type = 'string';

            $field = new DBModelField($fName, $field['type']);

            if ($field->typeIsInteger())
                $type = 'integer';

            if ($field->typeIsFloat())
                $type = 'float';

            if ($field->typeIsBoolean())
                $type = 'boolean';

            $fields_content .= "\r\n    /** @var $type */\r\n    public $$fName;";
        }

        $content = <<<XML
<?php


namespace App\Entity;

use Copper\Entity\AbstractEntity;

$use_state_fields_trait_class

class $name extends AbstractEntity
{
    // >>> Auto Generated: Fields
    $use_state_fields_trait
$fields_content
    // <<< -------------------
    
}
XML;

        if (FileHandler::fileExists($filePath)) {
            $old_content = FileHandler::getContent($filePath)->result;

            $old_content = self::updateClassUsage($old_content, EntityStateFields::class, $use_state_fields);

            $fields = StringHandler::regex($old_content, '/>>> Auto Generated: Fields(.*?)\/\/ <<</ms');
            $old_content = str_replace($fields, "\r\n" . self::T . "$use_state_fields_trait\r\n$fields_content\r\n" . self::T, $old_content);

            FileHandler::setContent($filePath, $old_content);
        } else {
            FileHandler::setContent($filePath, $content);
        }

        return $response->ok();
    }

    private static function updateClassUsage($content, $className, $enable = true)
    {
        $classPointer = StringHandler::regex($content, '/class (.*) extends AbstractEntity/m', 0, 0);
        $useClassNameStr = "use $className;";

        if ($enable) {
            if (strpos($content, $useClassNameStr) === false)
                $content = str_replace($classPointer, "$useClassNameStr\r\n\r\n$classPointer", $content);
        } else {
            $content = str_replace("$useClassNameStr\r\n\r\n", "", $content);
            $content = str_replace("$useClassNameStr\r\n", "", $content);
            $content = str_replace("$useClassNameStr\n\n", "", $content);
            $content = str_replace("$useClassNameStr\n", "", $content);
            $content = str_replace("$useClassNameStr", "", $content);
        }

        return $content;
    }
}