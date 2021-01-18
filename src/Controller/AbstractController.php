<?php

namespace Copper\Controller;

use Copper\Component\Auth\AuthHandler;
use Copper\Component\CP\CPHandler;
use Copper\Component\DB\DBHandler;
use Copper\Component\FlashMessage\FlashMessageHandler;
use Copper\Component\Templating\ViewHandler;
use Copper\RequestTrait;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\Session;
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
    /** @var Session */
    protected $session;
    /** @var FlashMessageHandler */
    protected $flashMessage;
    /** @var AuthHandler */
    protected $auth;
    /** @var DBHandler */
    protected $db;
    /** @var CPHandler */
    protected $cp;

    /**
     * AbstractController constructor.
     * @param Request $request
     * @param RequestContext $requestContext
     * @param RouteCollection $routes
     * @param AuthHandler $auth
     * @param FlashMessageHandler $flashMessage
     * @param DBHandler $db
     * @param CPHandler $cp
     */
    function __construct(Request $request, RequestContext $requestContext, RouteCollection $routes, AuthHandler $auth,
                         FlashMessageHandler $flashMessage, DBHandler $db, CPHandler $cp)
    {
        $this->request = $request;
        $this->requestContext = $requestContext;
        $this->routes = $routes;
        $this->flashMessage = $flashMessage;
        $this->auth = $auth;
        $this->db = $db;
        $this->cp = $cp;
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
        $templateHandler = new ViewHandler($this->request, $this->requestContext, $this->routes, $this->flashMessage, $this->auth, $parameters);

        return $templateHandler->render($view);
    }

    /**
     * Returns a HTTP Response
     *
     * @param mixed $data The response data
     * @param int $status The status code to use for the Response
     * @param array $headers Array of extra headers to add
     *
     * @return Response
     */
    protected function response($data, $status = 200, $headers = [])
    {
        return new Response($data, $status, $headers);
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
     * Dumps data in formatted style
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function dump($data, $echo = true)
    {
        $dump = "<pre>" . print_r($data, true) . "</pre>";

        if ($echo === true)
            echo $dump;

        return $dump;
    }

    /**
     * Dumps data in formatted style as response
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    protected function dump_response($data, $status = 200, $headers = [])
    {
        return new Response($this->dump($data, false), $status, $headers);
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


    /**
     * Redirects to authorization/login page with returnToRoute parameter as query string
     * If User is authorized already - show forbidden page
     *
     * @param string $returnToRoute
     *
     * @return RedirectResponse|Response
     */
    protected function authForbid(string $returnToRoute = '')
    {
        $authConfig = $this->auth->config;

        if ($this->auth->check() === true)
            return $this->render($authConfig->forbiddenTemplate);

        $parameters = [];

        if ($returnToRoute !== '')
            $parameters = [$authConfig->returnToRouteParam => $returnToRoute];

        return $this->redirectToRoute($authConfig->loginRoute, $parameters);
    }
}
