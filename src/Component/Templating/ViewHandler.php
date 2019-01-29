<?php

namespace Copper\Component\Templating;

use Copper\RequestTrait;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
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

    function __construct(Request $request, RequestContext $requestContext, RouteCollection $routes, array $parameters)
    {
        $this->requestContext = $requestContext;
        $this->routes = $routes;

        $this->postBag = $request->request;
        $this->queryBag = $request->query;
        $this->cookiesBag = $request->cookies;
        $this->routeBag = new ParameterBag($request->attributes->get('_route_params'));
        $this->dataBag = new ParameterBag($parameters);

        $this->request_method = $request->getRealMethod();
        $this->client_ip = $request->getClientIp();
        $this->controller_name = $request->attributes->get('_controller');
        $this->route_name = $request->attributes->get('_route');

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
     * Route parameter by key
     *
     * @param string $key
     * @param string|null $default
     *
     * @return string
     */
    public function route($key, $default = null)
    {
        return $this->routeBag->get($key, $default);
    }

    /**
     * POST parameter by key
     *
     * @param string $key
     * @param string|null $default
     *
     * @return string
     */
    public function post($key, $default = null)
    {
        return $this->postBag->get($key, $default);
    }

    /**
     * GET parameter by key
     *
     * @param string $key
     * @param string|null $default
     *
     * @return string
     */
    public function query($key, $default = null)
    {
        return $this->queryBag->get($key, $default);
    }

    /**
     * Cookies parameter by key
     *
     * @param string $key
     * @param string|null $default
     *
     * @return string
     */
    public function cookies($key, $default = null)
    {
        return $this->cookiesBag->get($key, $default);
    }

    /**
     * Template parameter by key
     *
     * @param string $key
     * @param string|null $default
     *
     * @return string
     */
    public function data($key, $default = null)
    {
        return $this->dataBag->get($key, $default);
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
     * Render Template
     *
     * @param $template
     *
     * @return string
     */
    public function render($template)
    {
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
