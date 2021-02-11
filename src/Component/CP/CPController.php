<?php

namespace Copper\Component\CP;

use Copper\Component\CP\Service\ResourceGenService;
use Copper\Component\DB\DBService;
use Copper\Controller\AbstractController;
use Copper\FileHandler;
use Copper\Kernel;
use Copper\Resource\AbstractResource;
use Copper\Test\DB\TestDB;

class CPController extends AbstractController
{
    const ACTION_AUTHORIZE = 'authorize';
    const ACTION_DB_MIGRATE = 'db_migrate';
    const ACTION_DB_SEED = 'db_seed';
    const ACTION_DB_GEN_MODEL_FIELDS = 'gen_model_fields';
    const ACTION_DB_TEST = 'db_test';
    const ACTION_DB_GENERATOR = 'db_generator';
    const ACTION_DB_GENERATOR_RUN = 'db_generator_run';
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
            case self::ACTION_DB_GENERATOR:
                return $this->db_generator();
                break;
            case self::ACTION_DB_GENERATOR_RUN:
                return $this->db_generator_run();
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

    private function db_generator()
    {
        $resourceList = FileHandler::getClassNamesInFolder(Kernel::getProjectPath() . '/src/Resource')->result;
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

        return $this->viewResponse('cp/generator', [
            'default_varchar_length' => $this->db->config->default_varchar_length,
            'resource_list' => $resourceList,
            'resource' => $resource
        ]);
    }

    private function db_generator_run()
    {
        $content = $this->request->getContent();

        $response = ResourceGenService::run($content);

        return $this->json($response);
    }
}
