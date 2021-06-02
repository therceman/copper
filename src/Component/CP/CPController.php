<?php

namespace Copper\Component\CP;

use Copper\Component\CP\Service\ResourceGenService;
use Copper\Component\DB\DBService;
use Copper\Component\HTML\HTML;
use Copper\Controller\AbstractController;
use Copper\Handler\ArrayHandler;
use Copper\Handler\CollectionHandler;
use Copper\Handler\FileHandler;
use Copper\Handler\StringHandler;
use Copper\Kernel;
use Copper\Resource\AbstractResource;
use Copper\Test\DB\TestDB;
use Copper\Test\TestCore;
use Copper\Test\TestValidator;
use Copper\Traits\ResourceControllerActions;

class CPController extends AbstractController
{
    const ACTION_AUTHORIZE = 'authorize';
    const ACTION_DB_MIGRATE = 'db_migrate';
    const ACTION_DB_SEED = 'db_seed';
    const ACTION_DB_TEST = 'db_test';
    const ACTION_CORE_TEST = 'core_test';
    const ACTION_VALIDATOR_TEST = 'validator_test';
    const ACTION_DB_GENERATOR = 'db_generator';
    const ACTION_DB_GENERATOR_RUN = 'db_generator_run';
    const ACTION_DB_GENERATOR_DEL = 'db_generator_del';
    const ACTION_ROUTE_LIST_UPDATE = 'route_list_update';
    const ACTION_ROUTE_ADD = 'route_add';
    const ACTION_LOGOUT = 'logout';

    private function hasAccess()
    {
        return $this->auth->session->get($this->cp->config->session_key, false);
    }

    public function getIndex()
    {
        if ($this->hasAccess() === false)
            return $this->viewResponse('cp/login');

        $entity_list = DBService::getClassNames('Entity')->result;

        return $this->viewResponse('cp/index', ['entity_list' => $entity_list]);
    }

    public function postAction($action)
    {
        switch ($action) {
            case self::ACTION_AUTHORIZE:
                return $this->authorize();
                break;
            case self::ACTION_LOGOUT:
                return $this->logout();
                break;
            case self::ACTION_DB_MIGRATE:
                return $this->db_migrate();
                break;
            case self::ACTION_DB_SEED:
                return $this->db_seed();
                break;
            case self::ACTION_DB_TEST:
                return $this->db_test();
                break;
            case self::ACTION_CORE_TEST:
                return $this->core_test();
                break;
            case self::ACTION_VALIDATOR_TEST:
                return $this->validator_test();
                break;
            case self::ACTION_DB_GENERATOR:
                return $this->db_generator();
                break;
            case self::ACTION_DB_GENERATOR_RUN:
                return $this->db_generator_run();
                break;
            case self::ACTION_DB_GENERATOR_DEL:
                return $this->db_generator_del();
                break;
            case self::ACTION_ROUTE_ADD:
                return $this->route_add();
                break;
            case self::ACTION_ROUTE_LIST_UPDATE:
                return $this->route_list_update();
                break;
        }

        $this->flashMessage->setError('Wrong Action Provided');

        return $this->redirectToRoute(ROUTE_get_copper_cp);
    }

    private function setSessionAuth(bool $hasAccess)
    {
        $this->auth->session->set($this->cp->config->session_key, $hasAccess);
    }

    private function logout()
    {
        $this->setSessionAuth(false);

        return $this->redirectToRoute(ROUTE_get_copper_cp);
    }

    private function authorize()
    {
        $password = $this->request->request->get($this->cp->config->password_field);

        $hasAccess = ($this->cp->config->password === $password);

        $this->setSessionAuth($hasAccess);

        if ($hasAccess === false)
            $this->flashMessage->setError('Wrong Auth');

        return $this->redirectToRoute(ROUTE_get_copper_cp);
    }

    private function db_migrate()
    {
        $result = DBService::migrate($this->db);

        echo '<pre>' . print_r($result, true) . '</pre>';

        return $this->response(PHP_EOL . '<br>ok');
    }

    private function db_seed()
    {
        $result = DBService::seed($this->db);

        echo '<pre>' . print_r($result, true) . '</pre>';

        return $this->response(PHP_EOL . '<br>ok');
    }

    private function db_test()
    {
        $test = new TestDB($this->db);

        $response = $test->run();

        return $this->dump_response($response);
    }

    private function core_test()
    {
        $test = new TestCore();

        $response = $test->run();

        return $this->dump_response($response);
    }

    private function validator_test()
    {
        $test = new TestValidator();

        $response = $test->run();

        return $this->dump_response($response);
    }

    private function db_generator()
    {
        $resourceList = FileHandler::getClassNamesInFolder(Kernel::getAppPath() . '/src/Resource')->result;
        /** @var AbstractResource $resource */
        $resource = $this->request->get('resource', null);

        $seed = $this->request->request->getBoolean('seed', false);
        $seed_force = $this->request->request->getBoolean('seed_force', false);

        $migrate = $this->request->request->getBoolean('migrate', false);
        $migrate_force = $this->request->request->getBoolean('migrate_force', false);

        if ($this->request->request->get('action') === 'prepare_templates') {
            $tpl_prepare_response = ResourceGenService::prepare_templates($resource, $this->request->request->get('force', false));
            return $this->json($tpl_prepare_response);
        }

        if ($this->request->request->get('action') === 'create_js_source_files') {
            $create_js_source_files_response = ResourceGenService::create_js_source_files($resource, $this->request->request->get('force', false));
            return $this->json($create_js_source_files_response);
        }

        if ($resource && $seed) {
            $seed_result = DBService::seedClassName($resource::getSeedClassName(), $this->db, ($seed_force !== false));
            $this->flashMessage->set('seed_result', $seed_result->msg);
            return $this->redirectToRoute(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR, 'resource' => $resource]);
        }

        if ($resource && $migrate) {
            $migrate_result = DBService::migrateClassName($resource::getModelClassName(), $this->db, ($migrate_force !== false));
            $this->flashMessage->set('migrate_result', $migrate_result->msg);
            return $this->redirectToRoute(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR, 'resource' => $resource]);
        }

        $db_column_list = [];
        if ($resource !== null)
            $db_column_list = $resource::getModel()->doGetColumns(true);

        // route info
        // ------------------------------------------

        $route_list = [];
        $route_group = false;
        $route_action_list = [];
        $action_list = ($resource !== null) ? get_class_methods($resource::getControllerClassName()) : null;

        if ($resource !== null) {

            $action_list = ($action_list !== null) ? $action_list : [];

            $trait_class_list = get_class_methods(ResourceControllerActions::class);

            try {
                $reflection_class = new \ReflectionClass($resource::getControllerClassName());
            } catch (\ReflectionException $e) {
                $reflection_class = null;
            }

            foreach ($action_list as $action) {
                $getMethod = substr($action, 0, 3) === 'get';
                $postMethod = substr($action, 0, 4) === 'post';

                try {
                    $params = [];
                    foreach ($reflection_class->getMethod($action)->getParameters() as $param) {
                        $params[] = '$' . $param->name;
                    };
                } catch (\ReflectionException $e) {
                    $params = [];
                }

                $name_with_params = $action . '(' . join(', ', $params) . ')';

                if (($getMethod || $postMethod) && ArrayHandler::hasValue($trait_class_list, $action) === false)
                    $route_action_list[] = ["name" => $action, "params" => $params,
                        "name_with_params" => $name_with_params,
                        "used" => false, "method" => ($getMethod) ? 'GET' : 'POST'];
            }

            foreach (Kernel::getRoutes()->all() as $name => $route) {
                $controller = $route->getDefaults()['_controller'][0];
                $controller_action = $route->getDefaults()['_controller'][1];

                if ($controller === $resource::getControllerClassName()) {
                    $route_group = $route->getDefault('_route_group');

                    $route_action_list_key = ArrayHandler::assocFindKey($route_action_list, ["name" => $controller_action]);
                    $route_action_list[$route_action_list_key]['used'] = true;

                    $method = $route->getMethods()[0];
                    $path = ($route_group !== null) ? str_replace($route_group . '/', '', $route->getPath()) : $route->getPath();
                    $name_without_group = (strtolower($method) === 'get') ? $route->getPath() : 'post@' . $route->getPath();

                    $route_list[] = ['action' => $controller_action, 'method' => $method, 'path' => $path,
                        'action_with_params' => $route_action_list[$route_action_list_key]['name_with_params'],
                        'name_without_group' => $name_without_group, 'name' => $name];
                }
            }
        }

        return $this->viewResponse('cp/generator', [
            'default_varchar_length' => $this->db->config->default_varchar_length,
            'resource_list' => $resourceList,
            'resource' => $resource,
            '$route_list' => $route_list,
            '$route_group' => $route_group,
            '$route_action_list' => $route_action_list,
            '$db_column_list' => $db_column_list
        ]);
    }

    private function db_generator_del()
    {
        $content = $this->request->getContent();

        $response = ResourceGenService::delete($content);

        return $this->json($response);
    }

    private function db_generator_run()
    {
        $content = $this->request->getContent();

        $response = ResourceGenService::run($content);

        return $this->json($response);
    }

    private function route_list_update()
    {
        $content = $this->requestJSON();

        $response = ResourceGenService::updateRouteList($content);

        return $this->json($response);
    }

    private function route_add()
    {
        $content = $this->requestJSON();

        $action = $content['action_with_params'];
        $method = $content['method'];
        $path = $content['path'];
        $name = $content['name'];

        $li = HTML::li()->id($name);

        $elMethod = HTML::select(['GET', 'POST'], "method[$name]", $method)->class('method')
            ->onInput('route_action_edit__method(this)');

        $elPath = HTML::input("path[$name]", $path)->class('path')
            ->onInput('route_action_edit__path(this)');

        $elAction = HTML::input("action[$name]", $action)->class('action')->disabled();

        $elDelete = HTML::button("Del", "del[$name]")->class('delete')
            ->onClick('route_action_edit__delete(this)');;

        $li->addElement($elMethod)->addElement($elPath)->addElement($elAction)->addElement($elDelete);

        return $this->json(['li' => $li]);
    }

}
