<?php

namespace Copper;

use Copper\Component\Auth\AuthHandler;
use Copper\Component\Auth\AuthPhpFileLoader;
use Copper\Component\CP\CPHandler;
use Copper\Component\CP\CPPhpFileLoader;
use Copper\Component\DB\DBHandler;
use Copper\Component\DB\DBPhpFileLoader;
use Copper\Component\FlashMessage\FlashMessageHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Loader\PhpFileLoader;

use Symfony\Component\Config\FileLocator;

class Kernel
{
    const CONFIG_FOLDER = 'config';
    const ROUTES_CONFIG_FILE = 'routes.php';
    const AUTH_CONFIG_FILE = 'auth.php';
    const DB_CONFIG_FILE = 'db.php';
    const CP_CONFIG_FILE = 'cp.php';

    /** @var RouteCollection */
    protected $routes;
    /** @var AuthHandler */
    protected $auth;
    /** @var FlashMessageHandler */
    protected $flashMessage;
    /** @var DBHandler */
    protected $db;
    /** @var CPHandler */
    protected $cp;

    public function __construct()
    {
        $this->configureRoutes();
        $this->configureDB();
        $this->configureAuth();
        $this->configureFlashMessage();
        $this->configureCP();
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

    /**
     * Returns path to project root directory
     *
     * @return string
     */
    public static function getProjectPath()
    {
        return dirname($_SERVER['SCRIPT_FILENAME']) . '/..';
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

        $matcher = new UrlMatcher($this->routes, $requestContext);

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
    protected function errorResponse($message, $status = 404)
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
            $instance = new $controller[0]($request, $requestContext, $this->routes, $this->auth, $this->flashMessage, $this->db, $this->cp);

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
        $this->routes = $loader->load($this::ROUTES_CONFIG_FILE);

        // Load application routes
        $path = $this::getProjectPath() . '/' . $this::CONFIG_FOLDER;
        if (file_exists($path . '/' . $this::ROUTES_CONFIG_FILE)) {
            $loader = new PhpFileLoader(new FileLocator($path));
            $this->routes->addCollection($loader->load($this::ROUTES_CONFIG_FILE));
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

        $this->auth = new AuthHandler($packageAuthConfig, $projectAuthConfig, $this->db);
    }

    /**
     * Initialize Flash Message
     */
    protected function configureFlashMessage()
    {
        $this->flashMessage = new FlashMessageHandler($this->auth->session);
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

        $this->db = new DBHandler($packageConfig, $projectConfig);
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

        $this->cp = new CPHandler($packageConfig, $projectConfig);

        if ($this->cp->config->enabled === false) {
            $this->routes->remove(ROUTE_get_copper_cp);
            $this->routes->remove(ROUTE_copper_cp_action);
        }
    }
}
