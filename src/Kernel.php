<?php

namespace Copper;

use Copper\Component\Mail\MailHandler;
use Copper\Component\Mail\MailPhpFileLoader;
use Copper\Component\Templating\ViewHandler;
use Copper\Handler\FileHandler;
use Copper\Component\Auth\AuthHandler;
use Copper\Component\Auth\AuthPhpFileLoader;
use Copper\Component\CP\CPHandler;
use Copper\Component\CP\CPPhpFileLoader;
use Copper\Component\DB\DBHandler;
use Copper\Component\DB\DBPhpFileLoader;
use Copper\Component\FlashMessage\FlashMessageHandler;
use Copper\Component\Validator\ValidatorHandler;
use Copper\Component\Validator\ValidatorPhpFileLoader;
use Copper\Resource\AbstractResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Loader\PhpFileLoader;

use Symfony\Component\Config\FileLocator;

final class Kernel
{
    const CONFIG_FOLDER = 'config';
    const ROUTES_FOLDER = 'routes';
    const SRC_RESOURCE_FOLDER = 'src/Resource';

    const ROUTES_CONFIG_FILE = 'routes.php';
    const AUTH_CONFIG_FILE = 'auth.php';
    const DB_CONFIG_FILE = 'db.php';
    const CP_CONFIG_FILE = 'cp.php';
    const MAIL_CONFIG_FILE = 'mail.php';
    const VALIDATOR_CONFIG_FILE = 'validator.php';

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
        $this->configureRoutes();
        $this->configureDB();
        $this->configureAuth();
        $this->configureFlashMessage();
        $this->configureCP();
        $this->configureValidator();
        $this->configureMail();
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
     * Returns client IP address
     *
     * @return string
     */
    public static function getIPAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public static function getProjectControllerPath()
    {
        return FileHandler::projectPathFromArray(['src', 'Controller']);
    }

    public static function getProjectServicePath()
    {
        return FileHandler::projectPathFromArray(['src', 'Service']);
    }

    public static function getProjectResourcePath()
    {
        return FileHandler::projectPathFromArray(['src', 'Resource']);
    }

    public static function getProjectEntityPath()
    {
        return FileHandler::projectPathFromArray(['src', 'Entity']);
    }

    public static function getProjectModelPath()
    {
        return FileHandler::projectPathFromArray(['src', 'Model']);
    }

    public static function getProjectSeedPath()
    {
        return FileHandler::projectPathFromArray(['src', 'Seed']);
    }

    public static function getProjectPublicPath()
    {
        return FileHandler::projectPathFromArray(['public']);
    }

    public static function getProjectTemplatesPath()
    {
        return FileHandler::projectPathFromArray(['templates']);
    }

    public static function getTemplatePath($template)
    {
        return FileHandler::pathFromArray([self::getProjectTemplatesPath(), $template . '.php']);
    }

    /**
     * Returns path to project root directory
     *
     * @return string
     */
    public static function getProjectPath()
    {
        return FileHandler::getAbsolutePath(dirname($_SERVER['SCRIPT_FILENAME']) . '/..');
    }

    /**
     * Returns path to package root directory
     *
     * @return string
     */
    public static function getPackagePath()
    {
        return dirname(__DIR__);
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
     * @return RequestContext
     */
    public static function getRequestContext(): RequestContext
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
            $response = $this->getRequestControllerResponse($request, $requestContext);
        } catch (\Exception $e) {
            if ($e instanceof MethodNotAllowedException) {
                $response = $this->errorResponse('Templating method [' . $request->getMethod() . '] is not allowed.');
            } else {
                $response = $this->errorResponse($e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Default Response for error
     *
     * @param string $message The response message
     * @param int $status The response status code
     *
     * @return Response
     */
    protected function errorResponse(string $message, $status = 404)
    {
        return new Response('<b>Error</b>: ' . $message, $status);
    }

    /**
     * Configure route parameters
     *
     * @param UrlMatcher $matcher
     * @param Request $request
     */
    protected function configureMatchedRequestAttributes(UrlMatcher $matcher, Request $request)
    {
        $routeDefinitionKeys = ['_controller', '_route'];

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
        if (is_array($controller) || (is_string($controller) && strpos($controller, '::') !== false)) {

            if (is_string($controller)) {
                $controller = explode('::', $controller);
            }

            // pass Templating and RequestContext initialized class to controller
            $instance = new $controller[0]($request, $requestContext,
                self::$routes, self::$auth, self::$flashMessage, self::$db, self::$cp, self::$validator, self::$mail);

            $controller = [$instance, $controller[1]];
        }

        if (!is_callable($controller)) {
            $response = $this->errorResponse('Controller is not callable', Response::HTTP_BAD_REQUEST);
        } else {
            $response = call_user_func_array($controller, $request->attributes->get('_route_params'));
        }

        return $response;
    }

    /**
     *  Configure default and application routes from {APP|CORE}/config/routes.php
     */
    protected function configureRoutes()
    {
        // Load default routes
        $path = $this::getPackagePath() . '/' . $this::CONFIG_FOLDER;
        $loader = new PhpFileLoader(new FileLocator($path));
        self::$routes = $loader->load($this::ROUTES_CONFIG_FILE);

        // Load application low level routes
        $path = $this::getProjectPath() . '/' . $this::ROUTES_FOLDER;
        if (file_exists($path)) {
            $files = array_diff(scandir($path), array('..', '.'));
            foreach ($files as $file) {
                $loader = new PhpFileLoader(new FileLocator($path));
                self::$routes->addCollection($loader->load($file));
            }
        }

        // Load application resource routes
        $path = $this::getProjectPath() . '/' . $this::SRC_RESOURCE_FOLDER;
        $resourceFiles = FileHandler::getFilesInFolder($path);

        foreach ($resourceFiles->result as $key => $resourceFile) {
            $filePath = $path . '/' . $resourceFile;

            /** @var AbstractResource $resourceClass */
            $resourceClass = FileHandler::getFileClassName($filePath);

            if (in_array('registerRoutes', get_class_methods($resourceClass)) === false)
                continue;

            $collection = new RouteCollection();
            $resourceClass::registerRoutes(new RoutingConfigurator($collection, $loader, $path, $filePath));
            $collection->addResource(new FileResource($filePath));

            self::$routes->addCollection($collection);
        }

        // Load application top level routes
        $path = $this::getProjectPath() . '/' . $this::CONFIG_FOLDER;
        if (file_exists($path . '/' . $this::ROUTES_CONFIG_FILE)) {
            $loader = new PhpFileLoader(new FileLocator($path));
            self::$routes->addCollection($loader->load($this::ROUTES_CONFIG_FILE));
        }
    }

    /**
     *  Configure default and application auth from {APP|CORE}/config/auth.php
     */
    protected function configureAuth()
    {
        $packagePath = $this::getPackagePath() . '/' . $this::CONFIG_FOLDER;
        $projectPath = $this::getProjectPath() . '/' . $this::CONFIG_FOLDER;

        $loader = new AuthPhpFileLoader(new FileLocator($packagePath));
        $packageAuthConfig = $loader->load($this::AUTH_CONFIG_FILE);

        $projectAuthConfig = null;
        if (file_exists($projectPath . '/' . $this::AUTH_CONFIG_FILE)) {
            $loader = new AuthPhpFileLoader(new FileLocator($projectPath));
            $projectAuthConfig = $loader->load($this::AUTH_CONFIG_FILE);
        }

        self::$auth = new AuthHandler($packageAuthConfig, $projectAuthConfig);
    }

    /**
     * Initialize Flash Message
     */
    protected function configureFlashMessage()
    {
        self::$flashMessage = new FlashMessageHandler(self::$auth->session);
    }

    /**
     *  Configure default and application database from {APP|CORE}/config/db.php
     */
    protected function configureDB()
    {
        $packagePath = $this::getPackagePath() . '/' . $this::CONFIG_FOLDER;
        $projectPath = $this::getProjectPath() . '/' . $this::CONFIG_FOLDER;

        $loader = new DBPhpFileLoader(new FileLocator($packagePath));
        $packageConfig = $loader->load($this::DB_CONFIG_FILE);

        $projectConfig = null;
        if (file_exists($projectPath . '/' . $this::DB_CONFIG_FILE)) {
            $loader = new DBPhpFileLoader(new FileLocator($projectPath));
            $projectConfig = $loader->load($this::DB_CONFIG_FILE);
        }

        self::$db = new DBHandler($packageConfig, $projectConfig);
    }

    /**
     *  Configure default and application Control Panel from {APP|CORE}/config/cp.php
     */
    protected function configureCP()
    {
        $packagePath = $this::getPackagePath() . '/' . $this::CONFIG_FOLDER;
        $projectPath = $this::getProjectPath() . '/' . $this::CONFIG_FOLDER;

        $loader = new CPPhpFileLoader(new FileLocator($packagePath));
        $packageConfig = $loader->load($this::CP_CONFIG_FILE);

        $projectConfig = null;
        if (file_exists($projectPath . '/' . $this::CP_CONFIG_FILE)) {
            $loader = new CPPhpFileLoader(new FileLocator($projectPath));
            $projectConfig = $loader->load($this::CP_CONFIG_FILE);
        }

        self::$cp = new CPHandler($packageConfig, $projectConfig);

        if (self::$cp->config->enabled === false) {
            self::$routes->remove(ROUTE_get_copper_cp);
            self::$routes->remove(ROUTE_copper_cp_action);
        }
    }

    /**
     *  Configure default and application Mail from {APP|CORE}/config/mail.php
     */
    protected function configureMail()
    {
        $packagePath = $this::getPackagePath() . '/' . $this::CONFIG_FOLDER;
        $projectPath = $this::getProjectPath() . '/' . $this::CONFIG_FOLDER;

        $loader = new MailPhpFileLoader(new FileLocator($packagePath));
        $packageConfig = $loader->load($this::MAIL_CONFIG_FILE);

        $projectConfig = null;
        if (file_exists($projectPath . '/' . $this::MAIL_CONFIG_FILE)) {
            $loader = new MailPhpFileLoader(new FileLocator($projectPath));
            $projectConfig = $loader->load($this::MAIL_CONFIG_FILE);
        }

        self::$mail = new MailHandler($packageConfig, $projectConfig);
    }

    /**
     *  Configure default and application Validator from {APP|CORE}/config/validator.php
     */
    protected function configureValidator()
    {
        $packagePath = $this::getPackagePath() . '/' . $this::CONFIG_FOLDER;
        $projectPath = $this::getProjectPath() . '/' . $this::CONFIG_FOLDER;

        $loader = new ValidatorPhpFileLoader(new FileLocator($packagePath));
        $packageConfig = $loader->load($this::VALIDATOR_CONFIG_FILE);

        $projectConfig = null;
        if (file_exists($projectPath . '/' . $this::VALIDATOR_CONFIG_FILE)) {
            $loader = new ValidatorPhpFileLoader(new FileLocator($projectPath));
            $projectConfig = $loader->load($this::VALIDATOR_CONFIG_FILE);
        }

        self::$validator = new ValidatorHandler($packageConfig, $projectConfig);
    }
}
