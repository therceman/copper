<?php

namespace Copper\Component\Templating;

use Copper\AppConfigurator;
use Copper\Component\Auth\AuthHandler;
use Copper\Component\FlashMessage\FlashMessageHandler;
use Copper\Component\HTML\HTML;
use Copper\Handler\FileHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;
use Copper\Kernel;
use Copper\RequestTrait;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ViewHandler
 * @package Copper\Component\Templating
 */
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
     * App Config
     *
     * @var AppConfigurator
     */
    public $appConfig;

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
        $this->appConfig = Kernel::getApp()->config;

        $this->requestContext = $requestContext;
        $this->routes = $routes;

        $this->postBag = $request->request;
        $this->queryBag = $request->query;
        $this->cookiesBag = $request->cookies;
        $this->routeBag = new ParameterBag($request->attributes->get('_route_params') ?? []);
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
            if (FileHandler::fileExists($filePath)) {
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
     * <hr>
     * <p>Check Priority:</p>
     * <p>1) Route</p>
     * <p>2) Template</p>
     * <p>3) Query</p>
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
        return VarHandler::isArray($value) ? $this->output->dump($value) : $this->output->text($value);
    }

    /**
     * Output data as JSON
     *
     * @param array|null|object|bool $value
     *
     * @return string
     */
    public function json($value)
    {
        return $this->output->json($value);
    }

    /**
     * Output data as JavaScript
     *
     * @param mixed $value <p>Any desired value</p>
     * @param bool $wrapIfStr [optional] = true <p>Wrap strings with single quotes</p>
     *
     * @return string
     */
    public function js($value, $wrapIfStr = true)
    {
        return $this->output->js($value, $wrapIfStr);
    }

    /**
     * @param string $html
     * @return string
     */
    public function injectCSRFTokenIntoHTML($html)
    {
        $csrf_token = Kernel::getAuth()->sessionId();

        $csrf_token_input = HTML::inputHidden(Kernel::CSRF_TOKEN, $csrf_token);

        $head_script = HTML::script('window.' . Kernel::CSRF_TOKEN . ' = "' . $csrf_token . '";');

        //  -------------- add CSRF_TOKEN as js variable --------------

        if (StringHandler::has($html, '<head>')) {
            $html = StringHandler::replace($html, '<head>', '<head>' . $head_script);
        } else {
            $html = $head_script . $html;
        }

        //  -------------- add CSRF_TOKEN to all forms --------------

        $html = StringHandler::replace($html, '</form>', $csrf_token_input . '</form>');

        //  -------------- delete CSRF_TOKEN from forms with method get --------------

        $script = 'document.querySelectorAll("form[method=get] input[name=' . Kernel::CSRF_TOKEN . ']")';
        $script .= '.forEach(function($el){$el.remove()});';
        $html .= HTML::script($script);

        return $html;
    }

    /**
     * Render Template
     *
     * @param string $template
     * @param array $parameters
     * @param bool $csrfProtection
     *
     * @return string
     */
    public function render(string $template, array $parameters = [], $csrfProtection = false)
    {
        foreach ($parameters as $key => $value) {
            $this->dataBag->set($key, $value);
        }

        $templateFilePath = $this->findTemplateFile($template);

        ob_start();

        if (!preg_match('/^[a-z0-9\-_\/]+$/i', $template)) {
            return $this->error('Template name contains wrong characters.');
        }

        if (FileHandler::fileExists($templateFilePath) === false) {
            return $this->error('Template [' . $template . $this::TEMPLATES_EXTENSION . '] not found.');
        }

        // this will shorten initialization of $view in template. E.g. <?php global $view
        $GLOBALS['view'] = $this;

        // eval is used to disable errors about Dynamic Require and Undefined Variables
        eval('$view = $this; require $templateFilePath;');

        $html = ob_get_clean();

        if ($csrfProtection)
            $html = $this->injectCSRFTokenIntoHTML($html);

        return $html;
    }
}
