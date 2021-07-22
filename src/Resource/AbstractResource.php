<?php


namespace Copper\Resource;


use App\Model\UserModel;
use Copper\Component\Auth\AbstractUserEntity;
use Copper\Handler\ArrayHandler;
use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBSeed;
use Copper\Entity\AbstractEntity;
use Copper\Handler\FileHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;
use Copper\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RouteConfigurator;

use Copper\Component\Routing\RoutingConfigurator;

abstract class AbstractResource
{
    /** @var array */
    private static $models = [];
    /** @var string[] */
    private static $groups = [];
    /** @var array[] */
    private static $accessRoles = [];
    /** @var array */
    private static $groupRequirements = [];
    /** @var array */
    private static $defaultDefinedVars = [];

    const GROUP = 'abstract_collection_resource';

    const GET_LIST = 'getList@/' . self::GROUP . '/list';
    const GET_EDIT = 'getEdit@/' . self::GROUP . '/edit/{id}';
    const POST_UPDATE = 'postUpdate@/' . self::GROUP . '/update/{id}';
    const GET_NEW = 'getNew@/' . self::GROUP . '/new';
    const POST_CREATE = 'postCreate@/' . self::GROUP . '/create';
    const POST_REMOVE = 'postRemove@/' . self::GROUP . '/remove/{id}';
    const POST_UNDO_REMOVE = 'postUndoRemove@/' . self::GROUP . '/remove/undo/{id}';

    /**
     * @param string|boolean $className
     *
     * @return string
     */
    private static function extractNameFromClassName($className)
    {
        return ($className === false) ? '' : ArrayHandler::lastValue(explode('\\', $className));
    }

    private static function getPhpFilePath(string $folderPath, string $name)
    {
        if (trim($name) === '')
            return '';

        return FileHandler::pathFromArray([$folderPath, $name . '.php']);
    }

    // --------------------------

    /**
     * @return string
     */
    abstract static function getModelClassName();

    static function getModelPath()
    {
        return self::getPhpFilePath(Kernel::getAppModelPath(), self::getModelName());
    }

    static function getModelName()
    {
        return self::extractNameFromClassName(static::getModelClassName());
    }

    // --------------------------

    /**
     * @return string
     */
    abstract static function getEntityClassName();

    static function getEntityPath()
    {
        return self::getPhpFilePath(Kernel::getAppEntityPath(), self::getEntityName());
    }

    static function getEntityName()
    {
        return self::extractNameFromClassName(static::getEntityClassName());
    }

    // --------------------------

    /**
     * @return string|false
     */
    static function getControllerClassName()
    {
        return false;
    }

    static function getControllerPath()
    {
        return self::getPhpFilePath(Kernel::getAppControllerPath(), self::getControllerName());
    }

    static function getControllerName()
    {
        return self::extractNameFromClassName(static::getControllerClassName());
    }

    // --------------------------

    /**
     * @return string|false
     */
    static function getServiceClassName()
    {
        return false;
    }

    static function getServicePath()
    {
        return self::getPhpFilePath(Kernel::getAppServicePath(), self::getServiceName());
    }

    static function getServiceName()
    {
        return self::extractNameFromClassName(static::getServiceClassName());
    }

    // --------------------------

    /**
     * @return string|false
     */
    static function getSeedClassName()
    {
        return false;
    }

    static function getSeedPath()
    {
        return self::getPhpFilePath(Kernel::getAppSeedPath(), self::getSeedName());
    }

    static function getSeedName()
    {
        return self::extractNameFromClassName(static::getSeedClassName());
    }

    // --------------------------

    static function getClassName()
    {
        return static::class;
    }

    static function getPath()
    {
        return self::getPhpFilePath(Kernel::getAppResourcePath(), self::getName());
    }

    static function getName()
    {
        return self::extractNameFromClassName(static::getClassName());
    }

    // --------------------------

    /**
     * @return mixed|false
     */
    static function getService()
    {
        return static::getServiceClassName();
    }

    /**
     * @return DBSeed|string
     */
    static function getSeed()
    {
        return static::getSeedClassName();
    }

    /**
     * @return AbstractEntity|false
     */
    static function getEntity()
    {
        return static::getEntityClassName();
    }

    /**
     * @return DBModel
     */
    public static function getModel()
    {
        $modelClassName = static::getModelClassName();

        if (array_key_exists(static::class, self::$models) === false)
            self::$models[static::class] = new $modelClassName();

        return self::$models[static::class];
    }

    // --------------------------

    /**
     * @param RoutingConfigurator $routes
     */
    public static function registerRoutes(RoutingConfigurator $routes)
    {
        // this should be overriden by child class
    }


    private static function extractActionAndPathFromRouteName(string $name)
    {
        $name = str_replace('@/', '@', $name);

        $nameParts = explode('@', $name);

        if (count($nameParts) === 1) {
            if ($name === '/')
                return ['getIndex', '/'];

            if ($name[0] === '/')
                ltrim($name, '/');

            $controllerActionPart = StringHandler::regexReplace($nameParts[0], '/{(.*)}/m', '');

            $controllerAction = 'get_' . str_replace('/', '_', $controllerActionPart);
            $controllerAction = StringHandler::underscoreToCamelCase($controllerAction);

            $nameParts = [$controllerAction, $nameParts[0]];
        } else {
            $controllerAction = false;

            $controllerActionPart = StringHandler::regexReplace($nameParts[1], '/{(.*)}/m', '');
            $controllerActionPart = str_replace('/', '_', $controllerActionPart);

            if (strlen($nameParts[0]) === 4 && substr($nameParts[0], 0, 4) === 'post') {
                $controllerAction = 'post_' . $controllerActionPart;
            } elseif (strlen($nameParts[0]) === 3 && substr($nameParts[0], 0, 3) === 'get') {
                $controllerAction = 'get_' . $controllerActionPart;
            }

            if ($controllerAction !== false)
                $nameParts = [StringHandler::underscoreToCamelCase($controllerAction), $nameParts[1]];
        }

        return $nameParts;
    }

    private static function getFullAccessRole($routeAccessRole)
    {
        $defaultAccessRole = Kernel::getAuth()->config->defaultAccessRole;

        $fullAccessRole = self::getAccessRole();

        if (ArrayHandler::hasValue($fullAccessRole, '*'))
            return [];

        if (ArrayHandler::hasValue($fullAccessRole, AbstractUserEntity::ROLE_SUPER_ADMIN) === false)
            $fullAccessRole[] = AbstractUserEntity::ROLE_SUPER_ADMIN;

        if (VarHandler::isArray($routeAccessRole) || VarHandler::isString($routeAccessRole)) {
            if ($routeAccessRole === '*')
                $fullAccessRole = [];
            else
                $fullAccessRole = ArrayHandler::merge($fullAccessRole,
                    VarHandler::isArray($routeAccessRole) ? $routeAccessRole : [$routeAccessRole]);
        }

        if ($defaultAccessRole !== false && $routeAccessRole !== '*')
            $fullAccessRole = ArrayHandler::merge_uniqueValues($fullAccessRole,
                VarHandler::isArray($defaultAccessRole) ? $defaultAccessRole : [$defaultAccessRole]);

        return $fullAccessRole;
    }

    /**
     * Helper for adding routes with less code.
     * $name format should be in the following format:
     * * getList@/product/getList - GET method for controller action named getList()
     * * remove@/product/remove/{id} - GET & POST methods for controller action named remove($id)
     *
     * <br>
     * For Example 'remove@/product/remove/{id}', is similar to this:
     * <code>
     * $routes->add('remove@/product/remove/{id}', '/product/remove/{id}')
     * ->controller([static::getControllerClassName(), 'remove'])
     * ->methods(['GET','POST']);
     * </code>
     *
     * <br>
     * Access Role examples: 'admin', ['admin', 'moderator']. For all users use: '*'
     * P.S. Access Role "super_admin" is added by default to all routes in resource
     *
     * @param RoutingConfigurator $routes
     * @param string $name
     * @param string|array $accessRole - Route access role
     *
     * @return RouteConfigurator
     */
    public static function addRoute(RoutingConfigurator $routes, string $name, $accessRole = null)
    {
        list($action, $path) = self::extractActionAndPathFromRouteName($name);

        // TODO maybe we should remove dots and join parts like camelcase...
        // e.g. /page/filename.xml -> getPageFilenameXml (instead of getPageFilename)
        $action = StringHandler::split($action, '.')[0];

        $methods = ['GET', 'POST'];

        if (substr($action, 0, 3) === 'get')
            $methods = ['GET'];
        elseif (substr($action, 0, 4) === 'post')
            $methods = ['POST'];

        $groupReq = self::getGroupRequirements();

        $fullAccessRole = self::getFullAccessRole($accessRole);

        $routeConfigurator = $routes->add(self::route($name), self::path($path))
            ->controller([static::getControllerClassName(), $action])
            ->methods($methods)->defaults([
                '_route_group' => self::getGroup(),
                '_route_access_role' => $fullAccessRole,
                '_resource' => static::class,
                '_default_defined_vars' => self::getDefaultDefinedVars()
            ]);

        if ($groupReq !== null && ArrayHandler::count($groupReq) > 0)
            $routeConfigurator->requirements($groupReq);

        return $routeConfigurator;
    }

    /**
     * @param string $group
     */
    public static function setGroup(string $group)
    {
        self::$groups[static::getName()] = $group;
    }

    /**
     * Set access role to all routes.
     * User with role super_admin will have access to all routes in resource
     * (even if this role is not provided in access role list)
     *
     * @param string|array $role
     */
    public static function setAccessRole($role)
    {
        self::$accessRoles[static::getName()] = VarHandler::isArray($role) ? $role : [$role];
    }

    /**
     * @param array $req
     */
    public static function setGroupRequirements(array $req)
    {
        self::$groupRequirements[static::getName()] = $req;
    }

    /**
     * @return string|null
     */
    public static function getGroup()
    {
        if (array_key_exists(static::getName(), self::$groups) === false)
            return null;

        return self::$groups[static::getName()];
    }

    /**
     * @return array
     */
    public static function getAccessRole()
    {
        if (array_key_exists(static::getName(), self::$accessRoles) === false)
            return [];

        return self::$accessRoles[static::getName()];
    }

    /**
     * @return array|null
     */
    public static function getGroupRequirements()
    {
        if (array_key_exists(static::getName(), self::$groupRequirements) === false)
            return null;

        return self::$groupRequirements[static::getName()];
    }

    /**
     * @param array $defaults
     */
    public static function setDefaultDefinedVars(array $defaults)
    {
        self::$defaultDefinedVars[static::getName()] = $defaults;
    }

    /**
     * @return array|false
     */
    public static function getDefaultDefinedVars()
    {
        if (array_key_exists(static::getName(), self::$defaultDefinedVars) === false)
            return [];

        $defaults = [];

        foreach (self::$defaultDefinedVars[static::getName()] as $key => $value) {
            $defaults[$key] = $value;
        }

        return $defaults;
    }

    /**
     * Returns route name with group
     *
     * @param string $name
     *
     * @return string
     */
    public static function route(string $name)
    {
        if (self::getGroup() === null)
            return $name;

        return self::getGroup() . '_' . $name;
    }

    private static function path(string $path)
    {
        if (self::getGroup() === null)
            return $path;

        $group = StringHandler::removeFirstChars(self::getGroup(), '/');
        $group = StringHandler::removeLastChars($group, '/');

        $path = StringHandler::removeFirstChars($path, '/');
        $path = StringHandler::removeLastChars($path, '/');

        if (trim($path) === '')
            return '/' . $group;

        return '/' . $group . '/' . $path;
    }

    /**
     * @param string $route
     * @param array $arguments
     *
     * @return ResourceUrl
     */
    protected static function url(string $route, array $arguments = [])
    {
        $arguments = ArrayHandler::merge(self::getDefaultDefinedVars(), $arguments);

        return ResourceUrl::create(self::route($route), $arguments);
    }
}