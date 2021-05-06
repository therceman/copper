<?php


namespace Copper\Traits;


use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBOrder;
use Symfony\Component\HttpFoundation\Response;

trait ResourceControllerActions
{
    /**
     * @return Response
     */
    public function getListAction()
    {
        $limit = $this->request->query->get('limit', 255);
        $offset = $this->request->query->get('offset', 0);
        $order = $this->request->query->get('order', DBOrder::ASC);
        $order_by = $this->request->query->get('order_by', DBModel::ID);
        $show_removed = $this->request->query->get('show_removed', false);

        $dbOrder = new DBOrder($order_by, (strtoupper($order) === DBOrder::ASC));

        $list = $this->service::getList($this->db, $limit, $offset, $dbOrder, $show_removed);

        return $this->viewResponse(self::TEMPLATE_LIST, ['list' => $list, 'resource' => $this->resource]);
    }

    /**
     * @return Response
     */
    public function getEditAction($id)
    {
        $entity = $this->service::get($this->db, $id);

        return $this->viewResponse(self::TEMPLATE_FORM, ['entity' => $entity, 'resource' => $this->resource]);
    }

    /**
     * @return Response
     */
    public function postUpdateAction($id)
    {
        $updateParams = $this->requestParamsExcluding(self::EXCLUDED_UPDATE_PARAMS);

        $validateResponse = $this->validator->validateModel($updateParams, $this->model);

        if ($validateResponse->hasError()) {
            $this->flashMessage->setError($validateResponse->msg);
        } else {
            $updateResponse = $this->service::update($this->db, $id, $updateParams);

            if ($updateResponse->hasError())
                $this->flashMessage->setError($updateResponse->msg);
        }

        return $this->redirectToRoute($this->resource::GET_EDIT, ['id' => $id]);
    }

    /**
     * @return Response
     */
    public function getNewAction()
    {
        return $this->viewResponse(self::TEMPLATE_FORM, ['entity' => new $this->entity(), 'resource' => $this->resource]);
    }

    /**
     * @return Response
     */
    public function postCreateAction()
    {
        $createParams = $this->requestParamsExcluding(self::EXCLUDED_CREATE_PARAMS);

        $validateResponse = $this->validator->validateModel($createParams, $this->model);

        if ($validateResponse->hasError()) {
            $this->flashMessage->setError($validateResponse->msg);
        } else {
            $createResponse = $this->service::create($this->db, $this->entity::fromArray($createParams));

            if ($createResponse->hasError())
                $this->flashMessage->setError($createResponse->msg);
        }

        return $this->redirectToRoute($this->resource::GET_LIST);
    }

    /**
     * @return Response
     */
    public function postRemoveAction($id)
    {
        $removeResponse = $this->service::remove($this->db, $id);

        if ($removeResponse->hasError())
            $this->flashMessage->setError($removeResponse->msg);
        else {
            $this->flashMessage->setSuccess('Entity #' . $id . ' is successfully removed');
            $this->flashMessage->set('undo_id', $id);
        }

        return $this->redirectToRoute($this->resource::GET_LIST);
    }

    /**
     * @return Response
     */
    public function postUndoRemoveAction($id)
    {
        $response = $this->service::undoRemove($this->db, $id);

        if ($response->hasError())
            $this->flashMessage->setError($response->msg);
        else
            $this->flashMessage->setSuccess('Entity #' . $id . ' is restored and disabled');

        return $this->redirectToRoute($this->resource::GET_LIST);
    }
}