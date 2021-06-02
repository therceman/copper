<?php

namespace Copper;

use Copper\Component\Error\ErrorHandler;
use Copper\Component\Mail\MailHandler;
use Copper\Component\Routing\RoutingConfigLoader;
use Copper\Component\Templating\ViewHandler;
use Copper\Controller\AbstractController;
use Copper\Handler\ArrayHandler;
use Copper\Handler\FileHandler;
use Copper\Component\Auth\AuthHandler;
use Copper\Component\CP\CPHandler;
use Copper\Component\DB\DBHandler;
use Copper\Component\FlashMessage\FlashMessageHandler;
use Copper\Component\Validator\ValidatorHandler;
use Copper\Handler\NumberHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

final class Kernel
{
    const CONFIG_FOLDER = 'config';

    const ERROR_CONFIG_FILE = 'error.php';
    const APP_CONFIG_FILE = 'app.php';
    const ROUTES_CONFIG_FILE = 'routes.php';
    const AUTH_CONFIG_FILE = 'auth.php';
    const DB_CONFIG_FILE = 'db.php';
    const CP_CONFIG_FILE = 'cp.php';
    const MAIL_CONFIG_FILE = 'mail.php';
    const VALIDATOR_CONFIG_FILE = 'validator.php';

    /** @var ErrorHandler */
    private static $errorHandler;
    /** @var App */
    private static $app;
    /** @var RouteCollection */
    private static $routes;
    /** @var AuthHandler */
    private static $auth;
    /** @var FlashMessageHandler */
    private static $flashMessage;
    /** @var DBHandler */
    private static $db;
    /** @var CPHandler */
    private static $cp;
    /** @var MailHandler */
    private static $mail;
    /** @var ValidatorHandler */
    private static $validator;
    /** @var RequestContext */
    private static $requestContext;
    /** @var Request */
    private static $request;

    public function __construct()
    {
        $this->configureErrorHandler();
        $this->configureApp();
        $this->configureCP();
        $this->configureDB();
        $this->configureAuth();
        $this->configureFlashMessage();
        $this->configureValidator();
        $this->configureMail();
        $this->configureRoutes();
    }

    /**
     * Returns Base Uri
     * @param bool $relative
     * @return string
     */
    public static function getBaseUri($relative = false)
    {
        $currentPath = $_SERVER['PHP_SELF'];
        $pathInfo = pathinfo($currentPath);
        $hostName = $_SERVER['HTTP_HOST'];
        $protocol = $_SERVER['REQUEST_SCHEME'];

        $path = $pathInfo['dirname'];

        if ($relative)
            return $path;
        else
            return $protocol . '://' . $hostName . $path;
    }

    /**
     * Returns client IP addresses as array
     *
     * @return array
     */
    public static function getIPAddressList()
    {
        $ip_list = [];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_list[] = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_list[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_list[] = $_SERVER['REMOTE_ADDR'];
        }

        return $ip_list;
    }

    public static function getAppControllerPath()
    {
        return FileHandler::appPathFromArray(['src', 'Controller']);
    }

    public static function getAppServicePath()
    {
        return FileHandler::appPathFromArray(['src', 'Service']);
    }

    public static function getAppResourcePath()
    {
        return FileHandler::appPathFromArray(['src', 'Resource']);
    }

    public static function getAppEntityPath()
    {
        return FileHandler::appPathFromArray(['src', 'Entity']);
    }

    public static function getAppModelPath()
    {
        return FileHandler::appPathFromArray(['src', 'Model']);
    }

    public static function getAppSeedPath()
    {
        return FileHandler::appPathFromArray(['src', 'Seed']);
    }

    public static function getAppTraitsPath()
    {
        return FileHandler::appPathFromArray(['src', 'Traits']);
    }

    public static function getAppPublicPath($path)
    {
        $pathArray = FileHandler::extendPathArray(['public'], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppTemplatesPath()
    {
        return FileHandler::appPathFromArray(['templates']);
    }

    public static function getAppLogPath($logFile = null)
    {
        $pathArray = ['log'];

        if ($logFile !== null)
            $pathArray[] = $logFile;

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getTemplatePath($template)
    {
        return FileHandler::pathFromArray([self::getAppTemplatesPath(), $template . '.php']);
    }

    /**
     * Returns path to app root directory
     *
     * @param string|array|null $path [optional] = null
     * <p>Path to specific file or folder in App root directory</p>
     *
     * @return string
     */
    public static function getAppPath($path = null)
    {
        $abs_path = FileHandler::getAbsolutePath(dirname($_SERVER['SCRIPT_FILENAME']) . '/..');

        $pathArray = [$abs_path];

        if ($path !== null)
            $pathArray[] = VarHandler::isArray($path) ? FileHandler::pathFromArray($path) : $path;

        return FileHandler::pathFromArray($pathArray);
    }

    /**
     * Returns path to package root directory
     *
     * @param string|array|null $path [optional] = null
     * <p>Path to specific file or folder in Package root directory</p>
     *
     * @return string
     */
    public static function getPackagePath($path = null)
    {
        $pathArray = [dirname(__DIR__)];

        if ($path !== null)
            $pathArray[] = VarHandler::isArray($path) ? FileHandler::pathFromArray($path) : $path;

        return FileHandler::pathFromArray($pathArray);
    }

    /**
     * @return App
     */
    public static function getApp(): App
    {
        return self::$app;
    }

    /**
     * @return ErrorHandler
     */
    public static function getErrorHandler(): ErrorHandler
    {
        return self::$errorHandler;
    }

    /**
     * @return RouteCollection
     */
    public static function getRoutes(): RouteCollection
    {
        return self::$routes;
    }

    /**
     * @return AuthHandler
     */
    public static function getAuth(): AuthHandler
    {
        return self::$auth;
    }

    /**
     * @return FlashMessageHandler
     */
    public static function getFlashMessage(): FlashMessageHandler
    {
        return self::$flashMessage;
    }

    /**
     * @return DBHandler
     */
    public static function getDb(): DBHandler
    {
        return self::$db;
    }

    /**
     * @return CPHandler
     */
    public static function getCp(): CPHandler
    {
        return self::$cp;
    }

    /**
     * @return MailHandler
     */
    public static function getMail(): MailHandler
    {
        return self::$mail;
    }

    /**
     * @return ValidatorHandler
     */
    public static function getValidator(): ValidatorHandler
    {
        return self::$validator;
    }

    /**
     * @return RequestContext|null
     */
    public static function getRequestContext()
    {
        return self::$requestContext;
    }

    /**
     * @return Request
     */
    public static function getRequest(): Request
    {
        return self::$request;
    }

    /**
     * Render view and return it as string
     *
     * @param string $view
     * @param array $parameters
     *
     * @return string
     */
    public static function renderView(string $view, array $parameters = [])
    {
        return self::createViewHandler($parameters)->render($view);
    }

    /**
     * Creates View Handler
     *
     * @param $parameters
     *
     * @return ViewHandler
     */
    public static function createViewHandler($parameters)
    {
        return new ViewHandler(self::$request, self::$requestContext, self::$routes, self::$flashMessage, self::$auth, $parameters);
    }

    // --------------------------

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $name The name of the route
     * @param array $parameters An array of parameters
     * @param int $type The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     */
    private static function generateRouteUrl(string $name, $parameters = [], $type = UrlGenerator::ABSOLUTE_PATH)
    {
        $generator = new UrlGenerator(self::getRoutes(), self::getRequestContext());

        return $generator->generate($name, $parameters, $type);
    }

    /**
     * Returns a path relative to the current path, e.g. "../parent-file".
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    public static function getRouteRelativePath(string $name, $parameters = [])
    {
        return self::generateRouteUrl($name, $parameters, UrlGenerator::RELATIVE_PATH);
    }

    /**
     * Returns a scheme-relative URL for the given route, e.g. "//example.com/dir/file".
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    public static function getRouteNetworkPath(string $name, $parameters = [])
    {
        return self::generateRouteUrl($name, $parameters, UrlGenerator::NETWORK_PATH);
    }

    /**
     * Returns the URL (without the scheme and host) for the given route.
     * If withScheme is enabled, it'll create the URL (with scheme and host) for the given route.
     *
     * @param $name
     * @param array $parameters
     * @param bool $withScheme
     *
     * @return string
     */
    public static function getRouteUrl($name, $parameters = [], $withScheme = true)
    {
        $type = ($withScheme) ? UrlGenerator::ABSOLUTE_URL : UrlGenerator::ABSOLUTE_PATH;

        return self::generateRouteUrl($name, $parameters, $type);
    }

    /**
     * @param string $url
     * @param int $status
     *
     * @return RedirectResponse
     */
    public static function redirectToUrl(string $url, $status = Response::HTTP_FOUND)
    {
        return (new RedirectResponse($url, $status));
    }

    /**
     * @param string $route
     * @param array $parameters
     * @param int $status
     *
     * @return RedirectResponse
     */
    public static function redirectToRoute(string $route, array $parameters = [], $status = Response::HTTP_FOUND)
    {
        return self::redirectToUrl(self::getRouteUrl($route, $parameters), $status);
    }

    /**
     * Returns script execution time (in seconds) at current function call
     *
     * @param bool $format
     * @param int $formatDecimals
     *
     * @return float
     */
    public static function getScriptExecutionTime($format = true, $formatDecimals = 4)
    {
        $res = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

        return ($format) ? NumberHandler::format($res, $formatDecimals) : $res;
    }

    private function processRequest(Request &$request)
    {
        if (self::$app->config->trim_input === false)
            return;

        foreach ($request->request->all() as $key => $value) {
            $request->request->set($key, StringHandler::trim($value));
        }

        foreach ($request->query->all() as $key => $value) {
            $request->query->set($key, StringHandler::trim($value));
        }
    }

    /**
     * Handles Request
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $requestContext = new RequestContext();
        $requestContext->fromRequest($request);

        self::$request = $request;
        self::$requestContext = $requestContext;

        $matcher = new UrlMatcher(self::$routes, $requestContext);

        try {
            $this->configureMatchedRequestAttributes($matcher, $request);
            $this->processRequest($request);
            $response = $this->getRequestControllerResponse($request, $requestContext);
        } catch (\Exception $e) {
            if ($e instanceof MethodNotAllowedException) {
                $msg = 'Method [' . $request->getMethod() . '] is not allowed.';
                $response = self::$errorHandler->throwErrorAsResponse($msg, Response::HTTP_BAD_REQUEST);
            } else {
                $status = StringHandler::has($e->getMessage(), 'No routes found')
                    ? Response::HTTP_NOT_FOUND
                    : Response::HTTP_INTERNAL_SERVER_ERROR;

                $response = self::$errorHandler->throwErrorAsResponse($e->getMessage(), $status);
            }
        }

        return $response;
    }

    /**
     * Configure route parameters
     *
     * @param UrlMatcher $matcher
     * @param Request $request
     */
    protected function configureMatchedRequestAttributes(UrlMatcher $matcher, Request $request)
    {
        $routeDefinitionKeys = ['_controller', '_route', '_route_group'];

        $matchCollection = $matcher->match($request->getPathInfo());

        $routeDefinitionParams = array_intersect_key($matchCollection, array_flip($routeDefinitionKeys));
        $controllerParams = ['_route_params' => array_diff_key($matchCollection, $routeDefinitionParams)];

        $request->attributes->add(array_merge($routeDefinitionParams, $controllerParams));
    }

    /**
     * Returns Response from provided controller in routes configuration file
     *
     * @param Request $request
     * @param RequestContext $requestContext
     *
     * @return Response
     */
    protected function getRequestControllerResponse(Request $request, RequestContext $requestContext)
    {
        // controller as function
        $controller = $request->attributes->get('_controller');

        // controller as class. (e.g [DefaultController::class, 'index']) OR '\App\Controller\DefaultController::index')
        if (VarHandler::isArray($controller) || (is_string($controller) && strpos($controller, '::') !== false)) {

            if (is_string($controller)) {
                $controller = explode('::', $controller);
            }

            if (class_exists($controller[0]) === false) {
                $msg = 'Controller [' . $controller[0] . '] not found';
                return self::$errorHandler->throwErrorAsResponse($msg, Response::HTTP_NOT_FOUND);
            }

            // pass Templating and RequestContext initialized class to controller
            $instance = new $controller[0]($request, $requestContext,
                self::$routes, self::$auth, self::$flashMessage, self::$db, self::$cp, self::$validator, self::$mail);

            $controller = [$instance, $controller[1]];
        }

        if (!is_callable($controller)) {
            $msg = 'Controller is not callable';

            if (VarHandler::isArray($controller) && count($controller) === 2 && $controller[0] instanceof AbstractController)
                $msg = 'Controller [' . get_class($controller[0]) . '] doesn\'t have callable method [' . $controller[1] . ']';

            $response = self::$errorHandler->throwErrorAsResponse($msg, Response::HTTP_NOT_IMPLEMENTED);
        } else {
            $response = call_user_func_array($controller, $request->attributes->get('_route_params'));
        }

        return $response;
    }

    /**
     *  Configure App from {Package|App}/config/app.php
     */
    protected function configureApp()
    {
        self::$app = new App(self::APP_CONFIG_FILE);

        ini_set('serialize_precision', strval(self::$app->config->serialize_precision));
    }

    /**
     *  Configure default and application routes from {APP|CORE}/config/routes.php & resources
     */
    protected function configureRoutes()
    {
        $configLoader = new RoutingConfigLoader(
            $this::CONFIG_FOLDER,
            $this::ROUTES_CONFIG_FILE,
            $this::getAppResourcePath()
        );

        self::$routes = $configLoader->loadRoutes();
    }

    /**
     *  Configure Auth from {Package|App}/config/auth.php
     */
    protected function configureAuth()
    {
        self::$auth = new AuthHandler(self::AUTH_CONFIG_FILE);
    }

    /**
     * Initialize Flash Message
     */
    protected function configureFlashMessage()
    {
        self::$flashMessage = new FlashMessageHandler(self::$auth->session);
    }

    /**
     *  Configure Database from {Package|App}/config/db.php
     */
    protected function configureDB()
    {
        self::$db = new DBHandler(self::DB_CONFIG_FILE);
    }

    /**
     *  Configure Control Panel from {Package|App}/config/cp.php
     */
    protected function configureCP()
    {
        self::$cp = new CPHandler(self::CP_CONFIG_FILE);

        if (self::$cp->config->enabled === false) {
            self::$routes->remove(ROUTE_get_copper_cp);
            self::$routes->remove(ROUTE_copper_cp_action);
        }
    }

    /**
     *  Configure Mail from {Package|App}/config/mail.php
     */
    protected function configureMail()
    {
        self::$mail = new MailHandler(self::MAIL_CONFIG_FILE);
    }

    /**
     *  Configure ErrorHandler from {Package|App}/config/mail.php
     */
    protected function configureErrorHandler()
    {
        self::$errorHandler = new ErrorHandler(self::ERROR_CONFIG_FILE);
    }

    /**
     *  Configure Validator from {Package|App}/config/validator.php
     */
    protected function configureValidator()
    {
        self::$validator = new ValidatorHandler(self::VALIDATOR_CONFIG_FILE);
    }
}
