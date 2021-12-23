<?php

namespace Copper\Controller;

use Copper\AppConfigurator;
use Copper\Component\Auth\AuthHandler;
use Copper\Component\CP\CPHandler;
use Copper\Component\DB\DBHandler;
use Copper\Component\DB\DBModel;
use Copper\Component\FlashMessage\FlashMessageHandler;
use Copper\Component\Mail\MailHandler;
use Copper\Component\Templating\ViewHandler;
use Copper\Component\Validator\ValidatorHandler;
use Copper\Handler\ArrayHandler;
use Copper\Handler\DateHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;
use Copper\Kernel;
use Copper\RequestTrait;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
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
    public $request;
    /** @var RequestContext */
    public $requestContext;
    /** @var RouteCollection */
    public $routes;
    /** @var FlashMessageHandler */
    public $flashMessage;
    /** @var AuthHandler */
    public $auth;
    /** @var DBHandler */
    public $db;
    /** @var CPHandler */
    public $cp;
    /** @var ValidatorHandler */
    public $validator;
    /** @var MailHandler */
    public $mail;
    /** @var ParameterBag */
    public $viewDataBag;
    /** @var ParameterBag */
    public $routeDataBag;
    /** @var AppConfigurator */
    public $config;

    /**
     * Request Method GET or POST
     *
     * @var string
     */
    public $request_method;

    /**
     * Request URI
     *
     * @var string
     */
    public $request_uri;

    /**
     * Request REFERER
     *
     * @var string
     */
    public $request_referer;

    /**
     * Client's IP address
     *
     * @var null|string
     */
    public $client_ip;

    /**
     * Route name
     *
     * @var string
     */
    public $route_name;

    /**
     * AbstractController constructor.
     * @param Request $request
     * @param RequestContext $requestContext
     * @param RouteCollection $routes
     * @param AuthHandler $auth
     * @param FlashMessageHandler $flashMessage
     * @param DBHandler $db
     * @param CPHandler $cp
     * @param ValidatorHandler $validator
     * @param MailHandler $mail
     */
    function __construct(Request $request, RequestContext $requestContext, RouteCollection $routes,
                         AuthHandler $auth, FlashMessageHandler $flashMessage, DBHandler $db,
                         CPHandler $cp, ValidatorHandler $validator, MailHandler $mail)
    {
        $this->request = $request;
        $this->requestContext = $requestContext;
        $this->routes = $routes;
        $this->flashMessage = $flashMessage;
        $this->auth = $auth;
        $this->db = $db;
        $this->cp = $cp;
        $this->validator = $validator;
        $this->mail = $mail;
        $this->config = Kernel::getApp()->config;

        $this->viewDataBag = new ParameterBag([]);
        // TODO $request->attributes->get('_route_params') should be changed to $request->getRouteParams();
        $this->routeDataBag = new ParameterBag($request->attributes->get('_route_params') ?? []);

        $this->request_method = $request->getRealMethod();
        $this->request_uri = $request->getUri();
        $this->request_referer = $request->headers->get('referer');

        $this->client_ip = $request->getClientIp();
        $this->route_name = $request->attributes->get('_route');

        $this->init();
    }

    public function init()
    {
        // alias for __constructor (but without parameters)
    }

    public function getDefaultIndex(): Response
    {
        return $this->viewResponse(ROUTE_index);
    }

    /**
     * Returns a response with rendered view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     *
     * @return Response
     */
    public function viewResponse($view, $parameters = [])
    {
        return new Response($this->renderView($view, ArrayHandler::merge($this->viewDataBag->all(), $parameters)));
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

        return $templateHandler->render($view, [], true);
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
    protected function response($data = '', $status = 200, $headers = [])
    {
        return new Response($data, $status, $headers);
    }

    /**
     * Returns an empty HTTP Response
     *
     * @param int $status The status code should be 204 or 304
     * @param array $headers Array of extra headers to add
     *
     * @return Response
     */
    protected function empty_response($status = 204, $headers = [])
    {
        return new Response(null, $status, $headers);
    }

    /**
     * Returns an XML HTTP Response
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return Response
     */
    protected function xml($data, $status = 200, $headers = [])
    {
        $response = $this->response($data, $status, $headers);

        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
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
    protected function json($data = null, $status = 200, $headers = [])
    {
        $data = (array)$data;

        ArrayHandler::objectsToString($data);

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
     * Downloads the data as a file (attachment response)
     *
     * @param $data
     * @param $filename
     * @param string $contenType
     * @param int $status
     * @param array $headers
     * @return Response
     */
    protected function download_response($data, $filename, $contenType = 'application/octet-stream', $status = 200, $headers = [])
    {
        $filename = StringHandler::replace($filename, '"', '');

        $headers = ArrayHandler::merge([
            'Content-Type' => $contenType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ], $headers);

        return $this->response($data, $status, $headers);
    }

    protected function dump_request($dumpServerInfo = false)
    {
        $controllerInfo = $this->request->attributes->get('_controller');
        $controllerClass = $controllerInfo[0];
        $controllerMethod = $controllerInfo[1];

        $routeName = $this->request->attributes->get('_route');
        $routeParams = $this->request->attributes->get('_route_params');

        $bodyParams = $this->request->request->all();
        $queryParams = $this->request->query->all();
        $jsonParams = $this->requestJSON();
        $content = $this->request->getContent();
        $files = $this->request->files->all();

        $server = $this->request->server->all();
        $headers = $this->request->headers->all();
        $cookies = $this->request->cookies->all();

        $ip_list = Kernel::getIPAddressList();

        $contentType = $this->request->headers->get('content_type');
        $method = $this->request->getRealMethod();
        $uri = $this->request->getUri();
        $protocol = $this->request->getProtocolVersion();

        $out = 'IP: ' . ArrayHandler::join($ip_list) . "\r\n";
        $out .= 'Date: ' . DateHandler::dateTimeFromTimestamp($server['REQUEST_TIME']) . "\r\n";
        $out .= 'Request: ' . "$method $uri $protocol" . "\r\n";
        $out .= 'Controller: ' . "$controllerClass::$controllerMethod ($routeName)" . "\r\n";
        $out .= 'Content Type: ' . $contentType . "\r\n";

        if (VarHandler::isEmpty($routeParams) === false) {
            $out .= '##### Route Params' . "\r\n\r\n";

            foreach ($routeParams as $k => $v) {
                $out .= "$k: $v\r\n";
            }
        }

        if (VarHandler::isEmpty($queryParams) === false) {
            $out .= "\r\n##### Query Params \r\n\r\n";

            foreach ($queryParams as $k => $v) {
                $out .= "$k: $v\r\n";
            }
        }

        if (VarHandler::isEmpty($headers) === false) {
            $out .= "\r\n##### Headers \r\n\r\n";

            foreach ($headers as $k => $v) {
                $out .= "$k: $v[0]\r\n";
            }
        }

        if (VarHandler::isEmpty($cookies) === false) {
            $out .= "\r\n##### Cookies \r\n\r\n";

            foreach ($cookies as $k => $v) {
                $out .= "$k: $v\r\n";
            }
        }

        if (VarHandler::isEmpty($bodyParams) === false) {
            $out .= "\r\n##### Body Params \r\n\r\n";

            foreach ($bodyParams as $k => $v) {
                $out .= "$k: $v\r\n";
            }
        }

        if (VarHandler::isEmpty($jsonParams) === false) {
            $out .= "\r\n##### JSON Params \r\n\r\n";

            foreach ($jsonParams as $k => $v) {
                if (is_array($v))
                    $v = json_encode($v);

                if ($v === true)
                    $v = 'true';

                if ($v === false)
                    $v = 'false';

                if ($v === null)
                    $v = 'null';

                $out .= "$k: $v\r\n";
            }
        }

        if (VarHandler::isEmpty($files) === false) {
            $out .= "\r\n##### Files \r\n\r\n";
            foreach ($files as $k => $v) {
                /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $v */
                $out .= "$k: {$v->getClientOriginalName()} ({$v->getMimeType()}), {$v->getSize()} bytes \r\n";
            }
        }

        if (VarHandler::isEmpty($jsonParams) && VarHandler::isEmpty($bodyParams) && $content !== '') {
            $out .= "\r\n##### Content \r\n\r\n";
            $out .= $content . "\r\n";
        }

        if ($dumpServerInfo && VarHandler::isEmpty($server) === false) {
            $out .= "\r\n##### Server \r\n\r\n";
            foreach ($server as $k => $v) {
                $out .= "$k: $v\r\n";
            }
        }

        $out .= "\r\n----------------------------------------------------------------------------------------\r\n\r\n";

        return $out;
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
        return $this->redirect($this->url($route, $parameters), $status);
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
            return $this->viewResponse($authConfig->forbiddenTemplate);

        $parameters = [];

        if ($returnToRoute !== '')
            $parameters = [$authConfig->returnToRouteParam => $returnToRoute];

        return $this->redirectToRoute($authConfig->loginRoute, $parameters);
    }

    /**
     * Get uploaded file
     *
     * @param $key
     *
     * @return UploadedFile|null
     */
    protected function requestFile($key)
    {
        return $this->request->files->get($key, null);
    }

    /**
     * Get JSON body
     *
     * @return array|mixed
     */
    protected function requestJSON()
    {
        if ($this->request->getContentType() !== 'json')
            return [];

        $content = $this->request->getContent();

        if ($this->config->trim_input)
            $content = StringHandler::trimJSON($content);

        $data = json_decode($content, true);

        return ($data === null) ? [] : $data;
    }

    /**
     * Extract all provided POST params from request
     *
     * @param mixed $keys Keys - included params
     *
     * @return array
     */
    public function requestParams($keys = null)
    {
        $params = [];

        if ($keys !== null)
            $keys = (VarHandler::isArray($keys) === false) ? [$keys] : $keys;

        foreach ($this->request->request->all() as $requestKey => $requestValue) {
            if ($keys === null || VarHandler::isArray($keys) && ArrayHandler::hasValue($keys, $requestKey) !== false)
                $params[$requestKey] = $requestValue;
        }

        return $params;
    }

    /**
     * Extract all params from request excluding provided keys
     *
     * @param array $keys Keys - excluded params
     *
     * @return array
     */
    public function requestParamsExcluding(array $keys)
    {
        $params = [];

        foreach ($this->request->request->all() as $requestKey => $requestValue) {
            if (array_search($requestKey, $keys) !== false)
                continue;

            $params[$requestKey] = $requestValue;
        }

        return $params;
    }


    /**
     * Create hash from string
     *
     * @param string $str
     * @return string
     */
    public function hash(string $str)
    {
        return DBModel::hash($str);
    }
}
