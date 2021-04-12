<?php

namespace Copper\Component\Templating;

use Copper\Component\Auth\AuthHandler;
use Copper\Component\FlashMessage\FlashMessageHandler;
use Copper\RequestTrait;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class ViewHandler
{
    use RequestTrait;

    const TEMPLATES_FOLDER = 'templates';
    const TEMPLATES_EXTENSION = '.php';

    /** @var Request */
    protected $request;
    /** @var RequestContext */
    protected $requestContext;
    /** @var RouteCollection */
    protected $routes;
    /** @var Session */
    protected $session;

    /** @var FlashMessageHandler */
    public $flashMessage;

    /** @var AuthHandler */
    public $auth;

    /**
     * Route parameters (/{page}).
     *
     * @var ParameterBag
     */
    public $routeBag;

    /**
     * Request body parameters ($_POST).
     *
     * @var ParameterBag
     */
    public $postBag;

    /**
     * Query string parameters ($_GET).
     *
     * @var ParameterBag
     */
    public $queryBag;

    /**
     * Cookies parameters
     *
     * @var ParameterBag
     */
    public $cookiesBag;

    /**
     * View parameters
     *
     * @var ParameterBag
     */
    public $dataBag;

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
     * AbstractController name
     *
     * @var string
     */
    public $controller_name;

    /**
     * Route name
     *
     * @var string
     */
    public $route_name;

    /** @var ViewOutput */
    public $output;

    /**
     * ViewHandler constructor.
     *
     * @param Request $request
     * @param RequestContext $requestContext
     * @param RouteCollection $routes
     * @param FlashMessageHandler $flashMessage
     * @param AuthHandler $auth
     * @param array $parameters
     */
    function __construct(Request $request, RequestContext $requestContext, RouteCollection $routes, FlashMessageHandler $flashMessage, AuthHandler $auth, array $parameters)
    {
        $this->requestContext = $requestContext;
        $this->routes = $routes;

        $this->postBag = $request->request;
        $this->queryBag = $request->query;
        $this->cookiesBag = $request->cookies;
        $this->routeBag = new ParameterBag($request->attributes->get('_route_params'));
        $this->dataBag = new ParameterBag($parameters);

        $this->request_method = $request->getRealMethod();
        $this->request_uri = $request->getUri();
        $this->request_referer = $request->headers->get('referer');

        $this->client_ip = $request->getClientIp();
        $this->controller_name = $request->attributes->get('_controller');
        $this->route_name = $request->attributes->get('_route');

        $this->flashMessage = $flashMessage;
        $this->auth = $auth;

        $this->output = new ViewOutput();
    }

    /**
     * Find template file in {APP|CORE}/templates
     *
     * @param $template
     *
     * @return string|null
     */
    private function findTemplateFile($template)
    {
        $templateFolderPathArray = [
            'core' => dirname(__DIR__) . '/../../' . $this::TEMPLATES_FOLDER,
            'app' => dirname($_SERVER['SCRIPT_FILENAME']) . '/../' . $this::TEMPLATES_FOLDER,
        ];

        $templateFilePath = null;

        foreach ($templateFolderPathArray as $key => $path) {
            $filePath = $path . '/' . $template . $this::TEMPLATES_EXTENSION;
            if (file_exists($filePath)) {
                $templateFilePath = $filePath;
            }
        }

        return $templateFilePath;
    }

    /**
     * @param string $type
     * @param string $key
     * @param mixed| null $default
     *
     * @return mixed
     */
    private function getByType(string $type, string $key, $default = null)
    {
        $routeParam = $this->routeBag->$type($key, $default);
        $dataParam = $this->dataBag->$type($key, $default);
        $queryParam = $this->queryBag->$type($key, $default);

        if ($this->routeBag->has($key))
            return $routeParam;
        elseif ($this->dataBag->has($key))
            return $dataParam;
        else
            return $queryParam;
    }

    /**
     * Return Route / Template / Query parameter by key.
     * Check Priority: 1) Route -> 2) Template -> 3) Query.
     *
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        return $this->getByType('get', $key, $default);
    }

    /**
     * Return Route / Template / Query parameter by key as Boolean.
     * Check Priority: 1) Route -> 2) Template -> 3) Query.
     *
     * @param string $key
     * @param bool $default
     *
     * @return bool
     */
    public function getBoolean(string $key, bool $default = false)
    {
        return $this->getByType('getBoolean', $key, $default);
    }

    /**
     * Return Route / Template / Query parameter by key as Int.
     * Check Priority: 1) Route -> 2) Template -> 3) Query.
     *
     * @param string $key
     * @param int $default
     *
     * @return int
     */
    public function getInt(string $key, int $default = 0)
    {
        return $this->getByType('getInt', $key, $default);
    }

    /**
     * Return Route / Template / Query parameter by key as alphabetic characters and digits.
     * Check Priority: 1) Route -> 2) Template -> 3) Query.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function getAlnum(string $key, string $default = '')
    {
        return $this->getByType('getAlnum', $key, $default);
    }

    /**
     * Return Route / Template / Query parameter by key as alphabetic characters.
     * Check Priority: 1) Route -> 2) Template -> 3) Query.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function getAlpha(string $key, string $default = '')
    {
        return $this->getByType('getAlpha', $key, $default);
    }

    /**
     * Return Route / Template / Query parameter by key as alphabetic characters.
     * Check Priority: 1) Route -> 2) Template -> 3) Query.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function getDigits(string $key, string $default = '')
    {
        return $this->getByType('getDigits', $key, $default);
    }

    /**
     * Check if Template or Query String has parameter by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key)
    {
        return ($this->dataBag->has($key) || $this->queryBag->has($key));
    }

    /**
     * Escape HTML code and output as string or formatted array
     *
     * @param string|array $value
     *
     * @return string
     */
    public function out($value)
    {
        return is_array($value) ? $this->output->dump($value) : $this->output->text($value);
    }

    /**
     * Output data as JSON
     *
     * @param array|null|bool $value
     *
     * @return string
     */
    public function json($value)
    {
        if ($value === null || is_bool($value))
            return '{}';

        return $this->output->json($value);
    }

    /**
     * Output data as JavaScript
     *
     * @param mixed $value
     *
     * @return string
     */
    public function js($value)
    {
        if (is_array($value))
            return $this->json($value);

        return $this->output->js($value);
    }

    /**
     * Render Template
     *
     * @param string $template
     * @param array $parameters
     *
     * @return string
     */
    public function render(string $template, array $parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $this->dataBag->set($key, $value);
        }

        $templateFilePath = $this->findTemplateFile($template);

        ob_start();

        if (!preg_match('/^[a-z0-9\-_\/]+$/i', $template)) {
            return $this->error('Template name contains wrong characters.');
        }

        if (!file_exists($templateFilePath)) {
            return $this->error('Template [' . $template . $this::TEMPLATES_EXTENSION . '] not found.');
        }

        // eval is used to disable errors about Dynamic Require and Undefined Variables
        eval('$view = $this; require $templateFilePath;');

        $html = ob_get_clean();

        return $html;
    }
}
