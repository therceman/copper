<?php

namespace Copper;

use Copper\Component\AssetsManager\AssetsManager;
use Copper\Component\AssetsManager\AssetsManagerConfigurator;
use Copper\Component\Auth\AuthConfigurator;
use Copper\Component\CP\CPConfigurator;
use Copper\Component\DB\DBConfigurator;
use Copper\Component\Error\ErrorConfigurator;
use Copper\Component\Error\ErrorHandler;
use Copper\Component\Mail\MailConfigurator;
use Copper\Component\Mail\MailHandler;
use Copper\Component\Routing\RoutingConfigLoader;
use Copper\Component\Templating\ViewHandler;
use Copper\Component\Validator\ValidatorConfigurator;
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
use Copper\Resource\AbstractResource;
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
    const CSRF_TOKEN = '__csrf_token';
    const CSRF_TOKEN_HEADER = 'X-CSRF-TOKEN';

    const CONFIG_FOLDER = 'config';

    const CACHE_FOLDER = 'cache';
    const CACHE_INFO_FILE = '.info';
    const CACHE_CONFIG_FILE = 'config.cache';

    const HTACCESS_VAR__INDEX_REL_PATH = 'index_rel_path';

    const ERROR_CONFIG_FILE = 'error.php';
    const APP_CONFIG_FILE = 'app.php';
    const ROUTES_CONFIG_FILE = 'routes.php';
    const AUTH_CONFIG_FILE = 'auth.php';
    const DB_CONFIG_FILE = 'db.php';
    const CP_CONFIG_FILE = 'cp.php';
    const MAIL_CONFIG_FILE = 'mail.php';
    const VALIDATOR_CONFIG_FILE = 'validator.php';
    const ASSETS_CONFIG_FILE = 'assets.php';

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
    /** @var Component\AssetsManager\AssetsManager */
    private static $assetsManager;
    /** @var RequestContext */
    private static $requestContext;
    /** @var Request */
    private static $request;
    /** @var AbstractController */
    private static $controller;

    public function __construct()
    {
        $cacheInfo = $this->getCacheInfo();

        if ($cacheInfo->isOK()) {
            if ($this->createConfigFromCache())
                return true;
        }

        $this->configureErrorHandler();
        $this->configureApp();
        $this->configureCP();
        $this->configureDB();
        $this->configureAuth();
        $this->configureValidator();
        $this->configureMail();
        $this->configureAssetsManager();
        $this->configureRoutes();
        $this->configureFlashMessage();

        $this->saveConfigToCache();

        $this->initConfiguredModules();

        return true;
    }

    private function getConfigConstantList()
    {
        $files = [
            self::ERROR_CONFIG_FILE,
            self::APP_CONFIG_FILE,
            self::ROUTES_CONFIG_FILE,
            self::AUTH_CONFIG_FILE,
            self::DB_CONFIG_FILE,
            self::CP_CONFIG_FILE,
            self::MAIL_CONFIG_FILE,
            self::VALIDATOR_CONFIG_FILE,
            self::ASSETS_CONFIG_FILE,
        ];

        $list = [];

        foreach ($files as $file) {
            $configFilePath = Kernel::getPackagePath([self::CONFIG_FOLDER, $file]);
            $appFilePath = Kernel::getAppPath([self::CONFIG_FOLDER, $file]);

            $list = ArrayHandler::merge($list, FileHandler::getFileConstantList($configFilePath));
            $list = ArrayHandler::merge($list, FileHandler::getFileConstantList($appFilePath));
        }

        return $list;
    }

    private function initConfiguredModules()
    {
        // AssetsManager
        AssetsManager::init();
        // CP
        if (self::$cp->config->enabled === false) {
            self::$routes->remove(ROUTE_get_copper_cp);
            self::$routes->remove(ROUTE_copper_cp_action);
        }
        // App
        ini_set('serialize_precision', strval(self::$app->config->serialize_precision));
    }

    private function saveConfigToCache()
    {
        $configCache = [
            '$errorHandler' => serialize(self::$errorHandler->config),
            '$app' => serialize(self::$app->config),
            '$cp' => serialize(self::$cp->config),
            '$db' => serialize(self::$db->config),
            '$auth' => serialize(self::$auth->config),
            '$validator' => serialize(self::$validator->config),
            '$mail' => serialize(self::$mail->config),
            '$assetsManager' => serialize(self::$assetsManager->config),
            '$routes' => serialize(self::$routes),
            'constant_list' => $this->getConfigConstantList()
        ];

        $configFilePath = Kernel::getAppPath([self::CACHE_FOLDER, self::CACHE_CONFIG_FILE]);
        FileHandler::setContent($configFilePath, json_encode($configCache));
    }

    private function createConfigFromCache()
    {
        $configFilePath = Kernel::getAppPath([self::CACHE_FOLDER, self::CACHE_CONFIG_FILE]);
        $configCacheResp = FileHandler::getContent($configFilePath);

        if ($configCacheResp->hasError())
            return false;

        $configCache = json_decode($configCacheResp->result, true);

        $constantList = $configCache['constant_list'];

        foreach ($constantList as $constant => $value) {
            define($constant, $value);
        }

        /** @var ErrorConfigurator $errorConfigurator */
        $errorConfigurator = unserialize($configCache['$errorHandler']);

        /** @var AppConfigurator $appConfigurator */
        $appConfigurator = unserialize($configCache['$app']);

        /** @var CPConfigurator $cpConfigurator */
        $cpConfigurator = unserialize($configCache['$cp']);

        /** @var DBConfigurator $dbConfigurator */
        $dbConfigurator = unserialize($configCache['$db']);

        /** @var AuthConfigurator $authConfigurator */
        $authConfigurator = unserialize($configCache['$auth']);

        /** @var ValidatorConfigurator $validatorConfigurator */
        $validatorConfigurator = unserialize($configCache['$validator']);

        /** @var MailConfigurator $mailConfigurator */
        $mailConfigurator = unserialize($configCache['$mail']);

        /** @var AssetsManagerConfigurator $assetsManagerConfigurator */
        $assetsManagerConfigurator = unserialize($configCache['$assetsManager']);

        $this->configureErrorHandler($errorConfigurator);
        $this->configureApp($appConfigurator);
        $this->configureCP($cpConfigurator);
        $this->configureDB($dbConfigurator);
        $this->configureAuth($authConfigurator);
        $this->configureValidator($validatorConfigurator);
        $this->configureMail($mailConfigurator);
        $this->configureAssetsManager($assetsManagerConfigurator);

        self::$routes = unserialize($configCache['$routes']);

        // TODO possibly this should be removed, and resources should write/get info directly from routes
        // TODO [this will speed up loading time]
        // Prepare Resources
        // ----------------------------------------------------
        foreach (self::$routes as $name => $route) {
            /** @var AbstractResource $resource */
            $resource = $route->getDefault('_resource');

            if ($resource === null)
                continue;

            $group = $route->getDefault('_route_group');

            if ($group !== null)
                $resource::setGroup($group);

            $accessRole = $route->getDefault('_route_access_role');

            if ($accessRole !== null)
                $resource::setAccessRole($accessRole);

            $defaultDefinedVars = $route->getDefault('_default_defined_vars');

            if ($defaultDefinedVars !== null)
                $resource::setDefaultDefinedVars($defaultDefinedVars);
        }

        $this->configureFlashMessage();

        $this->initConfiguredModules();

        return true;
    }

    private function getCacheInfo()
    {
        $infoFilePath = Kernel::getAppCachePath(self::CACHE_INFO_FILE);

        FileHandler::createFolder(Kernel::getAppCachePath());

        $info = [
            "app_config_folder_mod_time" => FileHandler::getModTime(Kernel::getAppConfigPath()),
            "package_config_folder_mod_time" => FileHandler::getModTime(Kernel::getPackageConfigPath()),
            "res_folder_mod_time" => FileHandler::getModTime(Kernel::getAppResourcePath()),
        ];

        $infoFileResp = FileHandler::getContent($infoFilePath);

        if ($infoFileResp->isOK()) {
            $oldInfo = json_decode($infoFileResp->result, true);
            $cacheResetRequired = ArrayHandler::count(ArrayHandler::diff($info, $oldInfo)) > 0;
        } else {
            $cacheResetRequired = true;
        }

        FileHandler::setContent($infoFilePath, json_encode($info));

        return FunctionResponse::createSuccessOrError(!$cacheResetRequired, $info);
    }

    /**
     * Requirements Check. Package write permission
     * @return bool
     */
    private static function checkPackageWritePermission()
    {
        $packageTestFolderPath = Kernel::getPackagePath('package_write_permission_check');

        if (FileHandler::fileExists($packageTestFolderPath))
            return true;

        $res = @mkdir($packageTestFolderPath);

        if ($res === false)
            return false;

        return true;
    }

    /**
     * Check all requirements before running project
     * @return FunctionResponse
     */
    public static function checkRequirements()
    {
        $response = new FunctionResponse();

        if (self::checkPackageWritePermission() === false)
            return $response->error('checkPackageWritePermission');

        return $response->ok();
    }

    public static function run()
    {
        $requirementsCheckResponse = self::checkRequirements();
        if ($requirementsCheckResponse->hasError())
            return die('Error! ' . $requirementsCheckResponse->msg);

        $request = Request::createFromGlobals();

        $requestContext = self::configureRequestContext($request);

        self::$request = $request;
        self::$requestContext = $requestContext;

        $self = new self();

        return $self->handle($request, $requestContext)->send();
    }

    public static function getIndexRelPath()
    {
        $index_rel_path = getenv(self::HTACCESS_VAR__INDEX_REL_PATH);

        if ($index_rel_path === false)
            $index_rel_path = StringHandler::replace($_SERVER['SCRIPT_FILENAME'], self::getAppPath() . '/', '');

        return $index_rel_path;
    }

    /**
     * Returns Base Uri
     * @param bool $relative
     * @param null $requestContext
     * @return string
     */
    public static function getAppBaseUrl($relative = false, $requestContext = null)
    {
        $requestContext = $requestContext ?? self::$requestContext;

        $hostName = $requestContext->getHost();
        $protocol = $requestContext->getScheme();

        $index_rel_path = self::getIndexRelPath();

        $path = StringHandler::replace($_SERVER['SCRIPT_NAME'], $index_rel_path, '');

        $path = rtrim($path, '/');

        if ($relative)
            return $path;
        else
            return $protocol . '://' . $hostName . $path;
    }

    /**
     * @param null $path
     * @param false $relative
     * @return string
     */
    public static function getAppPublicUri($path = null, $relative = false)
    {
        $basePathArray = [self::getAppBaseUrl($relative), self::$app->config->public_rel_path];

        $pathArray = FileHandler::extendPathArray($basePathArray, $path);

        return FileHandler::pathFromArray($pathArray);
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

    public static function getAppConfigPath($path = null)
    {
        $pathArray = FileHandler::extendPathArray([self::CONFIG_FOLDER], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppCachePath($path = null)
    {
        $pathArray = FileHandler::extendPathArray([self::CACHE_FOLDER], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppControllerPath($path = null)
    {
        $pathArray = FileHandler::extendPathArray(['src', 'Controller'], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppServicePath($path = null)
    {
        $pathArray = FileHandler::extendPathArray(['src', 'Service'], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppResourcePath($path = null)
    {
        $pathArray = FileHandler::extendPathArray(['src', 'Resource'], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppEntityPath($path = null)
    {
        $pathArray = FileHandler::extendPathArray(['src', 'Entity'], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppModelPath($path = null)
    {
        $pathArray = FileHandler::extendPathArray(['src', 'Model'], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppSeedPath($path = null)
    {
        $pathArray = FileHandler::extendPathArray(['src', 'Seed'], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppTraitsPath($path = null)
    {
        $pathArray = FileHandler::extendPathArray(['src', 'Traits'], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppPublicPath($path = null)
    {
        $pathArray = FileHandler::extendPathArray([self::$app->config->public_rel_path], $path);

        return FileHandler::appPathFromArray($pathArray);
    }

    public static function getAppTemplatesPath($path = null)
    {
        $pathArray = FileHandler::extendPathArray(['templates'], $path);

        return FileHandler::appPathFromArray($pathArray);
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
        $index_rel_path = getenv(self::HTACCESS_VAR__INDEX_REL_PATH);

        if ($index_rel_path === false) {
            $abs_path = FileHandler::getAbsolutePath(__DIR__ . '/../../../../');
        } else {
            $abs_path = StringHandler::replace($_SERVER['SCRIPT_FILENAME'], '/' . $index_rel_path, '');
        }

        $pathArray = FileHandler::extendPathArray([$abs_path], $path);

        return FileHandler::pathFromArray($pathArray);
    }

    public static function getPackageConfigPath($path = null)
    {
        $pathArray = FileHandler::extendPathArray([self::CONFIG_FOLDER], $path);

        return FileHandler::packagePathFromArray($pathArray);
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
        $pathArray = FileHandler::extendPathArray([dirname(__DIR__)], $path);

        return FileHandler::pathFromArray($pathArray);
    }

    /**
     * @return App|null
     */
    public static function getApp()
    {
        return self::$app;
    }

    /**
     * @return ErrorHandler|null
     */
    public static function getErrorHandler()
    {
        return self::$errorHandler;
    }

    /**
     * @return AssetsManager|null
     */
    public static function getAssetsManager()
    {
        return self::$assetsManager;
    }

    /**
     * @return RouteCollection|null
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * @return AuthHandler|null
     */
    public static function getAuth()
    {
        return self::$auth;
    }

    /**
     * @return AbstractController|null
     */
    public static function getController()
    {
        return self::$controller;
    }

    /**
     * @return FlashMessageHandler|null
     */
    public static function getFlashMessage()
    {
        return self::$flashMessage;
    }

    /**
     * @return DBHandler|null
     */
    public static function getDb()
    {
        return self::$db;
    }

    /**
     * @return CPHandler|null
     */
    public static function getCp()
    {
        return self::$cp;
    }

    /**
     * @return MailHandler|null
     */
    public static function getMail()
    {
        return self::$mail;
    }

    /**
     * @return ValidatorHandler|null
     */
    public static function getValidator()
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
     * @return Request|null
     */
    public static function getRequest()
    {
        return self::$request;
    }

    /**
     * @param string $msg
     * @param array|object|string|int|float|bool|null $data
     * @param int $status
     *
     * @return FunctionResponse
     */
    public static function logError(string $msg, $data = null, int $status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return Kernel::getErrorHandler()->logError($msg, $data, $status);
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

    /**
     * @param Request $request
     */
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
     * @param $request
     * @return RequestContext
     */
    private static function configureRequestContext($request)
    {
        $requestContext = new RequestContext();

        $requestContext->fromRequest($request);

        $host = $_SERVER['HTTP_HOST'];

        if (isset($_SERVER['SCRIPT_URI']))
            $host = parse_url($_SERVER['SCRIPT_URI'], PHP_URL_HOST) ?? $host;

        $requestContext->setHost($host);

        $base_url = self::getAppBaseUrl(true, $requestContext);

        if ($base_url !== '' && $base_url !== '/') {
            $requestContext->setBaseUrl($base_url);
            // remove base from path
            $path_info = str_replace($base_url, '', $_SERVER['REQUEST_URI']);
        } else {
            $path_info = $_SERVER['REQUEST_URI'];
        }

        // remove query string
        $path_info = explode('?', $path_info)[0];

        // remove trailing slashes
        $path_info = ltrim($path_info, '/');
        $path_info = rtrim($path_info, '/');

        $requestContext->setPathInfo('/' . $path_info);

        return $requestContext;
    }

    // TODO should be moved to ENV module
    public static function isLocalhost()
    {
        $host_ip = $_SERVER['SERVER_ADDR'];

        return ($host_ip === '127.0.0.1' || $host_ip === '::1');
    }

    /**
     * Returns CSRF Token value
     * @return string
     */
    public static function getCSRFToken()
    {
        return Kernel::getAuth()->sessionId();
    }

    /**
     * CSRF Verification
     *
     * @param Request $request
     * @return FunctionResponse
     */
    private function verifyCSRFToken(Request $request)
    {
        $response = new FunctionResponse();

        $isXmlHttpRequest = $request->isXmlHttpRequest();
        $csrf_token = $request->request->get(Kernel::CSRF_TOKEN, null);

        if ($csrf_token === null)
            $csrf_token = $request->headers->get(Kernel::CSRF_TOKEN_HEADER);

        if ($csrf_token !== self::getCSRFToken() && ($isXmlHttpRequest || $request->getMethod() === 'POST')) {
            $msg = 'CSRF Verification Failed for method [' . $request->getMethod() . ']';

            if ($isXmlHttpRequest)
                $msg .= ' @ XMLHttpRequest';

            return $response->error($msg);
        }

        return $response->success();
    }

    /**
     * Handles Request
     *
     * @param Request $request
     * @param RequestContext $requestContext
     *
     * @return Response
     */
    public function handle(Request $request, RequestContext $requestContext)
    {
        $matcher = new UrlMatcher(self::$routes, $requestContext);

        $csrfVerificationResp = $this->verifyCSRFToken($request);
        if ($csrfVerificationResp->hasError())
            return self::$errorHandler->throwErrorAsResponse($csrfVerificationResp->msg, Response::HTTP_FORBIDDEN);

        try {
            $this->configureMatchedRequestAttributes($matcher, $request, $requestContext);
            $this->processRequest($request);

            $request_access_roles = $request->attributes->get('_route_access_role', []);
            if (count($request_access_roles) > 0 && self::$auth->user()->hasRole($request_access_roles) === false)
                $response = self::$errorHandler->throwErrorAsResponse('Access Denied', Response::HTTP_FORBIDDEN);
            else
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

        // if response is not returned from controller - create empty response
        if ($response instanceof Response === false)
            $response = new Response(null, 204);

        return self::prepareResponse($response, $request);
    }

    /**
     * Prepare Response before sending it to client
     *
     * @param Response $response
     * @param Request $request
     * @return Response
     */
    private function prepareResponse(Response $response, Request $request)
    {
        $response = $response->prepare($request);

        header_remove('X-Powered-By');

        return $response;
    }

    /**
     * Configure route parameters
     *
     * @param UrlMatcher $matcher
     * @param Request $request
     * @param RequestContext $requestContext
     */
    protected function configureMatchedRequestAttributes(UrlMatcher $matcher, Request $request, RequestContext $requestContext)
    {
        $routeDefinitionKeys = [
            '_controller',
            '_route',
            '_route_group',
            '_route_access_role',
            '_default_defined_vars',
            '_resource'
        ];

        $matchCollection = $matcher->match($requestContext->getPathInfo());

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

            self::$controller = $instance;

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
     * TODO should be moved to request itself
     *
     * Returns route data by key
     * @param string $key
     * @param null $defaultValue
     * @return mixed|null
     */
    public static function getRouteData(string $key, $defaultValue = null)
    {
        $controller = Kernel::getController();

        if ($controller === null)
            return $defaultValue;

        return $controller->routeDataBag->get($key, $defaultValue);
    }

    /**
     * Returns application config
     * If bagKey is provided - returns key value from config bag (custom values)
     * @param string|null $bagKey
     * @param mixed|null $defaultValue
     * @return AppConfigurator|mixed|null
     */
    public static function getAppConfig($bagKey = null, $defaultValue = null)
    {
        if ($bagKey !== null)
            return self::$app->config->bag->get($bagKey, $defaultValue);

        return self::$app->config;
    }

    /**
     *  Configure App from {Package|App}/config/app.php
     * @param AppConfigurator|null $config
     */
    protected function configureApp(AppConfigurator $config = null)
    {
        self::$app = new App(self::APP_CONFIG_FILE, $config);
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
     * @param AuthConfigurator|null $config
     */
    protected function configureAuth(AuthConfigurator $config = null)
    {
        self::$auth = new AuthHandler(self::AUTH_CONFIG_FILE, $config);
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
     * @param DBConfigurator|null $config
     */
    protected function configureDB(DBConfigurator $config = null)
    {
        self::$db = new DBHandler(self::DB_CONFIG_FILE, $config);
    }

    /**
     *  Configure Control Panel from {Package|App}/config/cp.php
     * @param CPConfigurator|null $config
     */
    protected function configureCP(CPConfigurator $config = null)
    {
        self::$cp = new CPHandler(self::CP_CONFIG_FILE, $config);
    }

    /**
     *  Configure Mail from {Package|App}/config/mail.php
     * @param MailConfigurator|null $config
     */
    protected function configureMail(MailConfigurator $config = null)
    {
        self::$mail = new MailHandler(self::MAIL_CONFIG_FILE, $config);
    }

    /**
     *  Configure ErrorHandler from {Package|App}/config/mail.php
     * @param ErrorConfigurator|null $config
     */
    protected function configureErrorHandler(ErrorConfigurator $config = null)
    {
        self::$errorHandler = new ErrorHandler(self::ERROR_CONFIG_FILE, $config);
    }

    /**
     *  Configure Validator from {Package|App}/config/validator.php
     * @param ValidatorConfigurator|null $config
     */
    protected function configureValidator(ValidatorConfigurator $config = null)
    {
        self::$validator = new ValidatorHandler(self::VALIDATOR_CONFIG_FILE, $config);
    }

    /**
     *  Configure Validator from {Package|App}/config/assets.php
     * @param AssetsManagerConfigurator|null $config
     */
    protected function configureAssetsManager(AssetsManagerConfigurator $config = null)
    {
        self::$assetsManager = new AssetsManager(self::ASSETS_CONFIG_FILE, $config);
    }
}
