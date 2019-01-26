<?php

namespace Copper\Controller;

use Copper\Component\Templating\ViewHandler;
use Copper\RequestTrait;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class AbstractController
{
    use RequestTrait;

    /** @var Request */
    protected $request;
    /** @var RequestContext */
    protected $requestContext;
    /** @var RouteCollection */
    protected $routes;

    function __construct($request, $requestContext, $routes)
    {
        $this->request = $request;
        $this->requestContext = $requestContext;
        $this->routes = $routes;
    }

    /**
     * Returns a response with rendered view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     *
     * @return Response
     */
    public function render($view, $parameters = [])
    {
        return new Response($this->renderView($view, $parameters));
    }

    /**
     * Returns a rendered view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     *
     * @return string The rendered view
     */
    protected function renderView($view, $parameters = [])
    {
        $templateHandler = new ViewHandler($this->request, $this->requestContext, $this->routes, $parameters);

        return $templateHandler->render($view);
    }

    /**
     * Returns a JsonResponse that uses json_encode.
     *
     * @param mixed $data The response data
     * @param int $status The status code to use for the Response
     * @param array $headers Array of extra headers to add
     *
     * @return JsonResponse
     */
    protected function json($data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param string $route The name of the route
     * @param array $parameters An array of parameters
     * @param int $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute($route, $parameters = [], $status = 302)
    {
        return $this->redirect($this->path($route, $parameters), $status);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url The URL to redirect to
     * @param int $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

}
