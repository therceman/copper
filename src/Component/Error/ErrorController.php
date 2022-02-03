<?php

namespace Copper\Component\Error;

use Copper\Component\CP\Service\ResourceGenService;
use Copper\Component\DB\DBService;
use Copper\Controller\AbstractController;
use Copper\Handler\ArrayHandler;
use Copper\Handler\CollectionHandler;
use Copper\Handler\FileHandler;
use Copper\Kernel;
use Copper\Resource\AbstractResource;
use Copper\Test\DB\TestDB;
use Copper\Test\TestCore;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    /**
     * @param array $parameters
     *
     * @return RedirectResponse|Response
     */
    public function viewErrorTemplate($parameters = [])
    {
        $flash_parameters = Kernel::getErrorHandler()->getFlashParameters();

        $parameters = ArrayHandler::merge($flash_parameters, $parameters);

        if (count($parameters) === 0)
            return $this->redirectToRoute(ROUTE_index);

        return new Response($this->renderView(Kernel::getErrorHandler()->config->view_default_template, $parameters));
    }

}
