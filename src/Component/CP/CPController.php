<?php

namespace Copper\Component\CP;

use Copper\Component\CP\DB\DBService;
use Copper\Controller\AbstractController;
use Copper\Entity\AbstractEntity;
use Copper\FunctionResponse;
use Copper\Kernel;

class CPController extends AbstractController
{
    const ACTION_AUTHORIZE = 'authorize';
    const ACTION_DB_MIGRATE = 'db_migrate';
    const ACTION_DB_SEED = 'db_seed';
    const ACTION_DB_GEN_MODEL_FIELDS = 'gen_model_fields';
    const ACTION_LOGOUT = 'logout';

    private function hasAccess()
    {
        return $this->auth->session->get($this->cp->config->session_key, false);
    }

    public function getIndex()
    {
        if ($this->hasAccess() === false)
            return $this->render('cp/login');

        $entity_list = DBService::getClassNames('Entity')->result;

        return $this->render('cp/index', ['entity_list' => $entity_list]);
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
            case self::ACTION_DB_GEN_MODEL_FIELDS:
                return $this->db_gen_model_fields();
                break;
        }

        $this->flashMessage->set('error', 'Wrong Action Provided');

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
            $this->flashMessage->set('error', 'Wrong Auth');

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

    private function db_gen_model_fields()
    {
        $response = new FunctionResponse();

        $className = $this->request->get('class_name');

        /** @var AbstractEntity $entity */
        $entity = new $className();

        $fields = $entity->toArray();

        $out = "\r\n\r\n";

        foreach ($fields as $fieldKey => $fieldValue) {
            $out .= 'const ' . strtoupper($fieldKey) . " = '$fieldKey';\r\n";
        }

        $response->success("ok", $out);

        echo '<pre>' . print_r($response, true) . '</pre>';

        return $this->response(PHP_EOL . '<br>ok');
    }
}
