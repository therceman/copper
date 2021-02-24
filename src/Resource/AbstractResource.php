<?php


namespace Copper\Resource;


use Copper\Handler\ArrayHandler;
use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBSeed;
use Copper\Entity\AbstractEntity;
use Copper\Handler\FileHandler;
use Copper\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RouteConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

abstract class AbstractResource
{
    /** @var array */
    private static $models = [];

    const PATH_GROUP = 'abstract_collection_resource';

    const GET_LIST = 'getList@/' . self::PATH_GROUP . '/list';
    const GET_EDIT = 'getEdit@/' . self::PATH_GROUP . '/edit/{id}';
    const POST_UPDATE = 'postUpdate@/' . self::PATH_GROUP . '/update/{id}';
    const GET_NEW = 'getNew@/' . self::PATH_GROUP . '/new';
    const POST_CREATE = 'postCreate@/' . self::PATH_GROUP . '/create';
    const POST_REMOVE = 'postRemove@/' . self::PATH_GROUP . '/remove/{id}';
    const POST_UNDO_REMOVE = 'postUndoRemove@/' . self::PATH_GROUP . '/remove/undo/{id}';

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
        return self::getPhpFilePath(Kernel::getProjectModelPath(), self::getModelName());
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
        return self::getPhpFilePath(Kernel::getProjectEntityPath(), self::getEntityName());
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
        return self::getPhpFilePath(Kernel::getProjectControllerPath(), self::getControllerName());
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
        return self::getPhpFilePath(Kernel::getProjectServicePath(), self::getServiceName());
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
        return self::getPhpFilePath(Kernel::getProjectSeedPath(), self::getSeedName());
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
        return self::getPhpFilePath(Kernel::getProjectResourcePath(), self::getName());
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

    static function registerRoutes(RoutingConfigurator $routes)
    {
        return $routes;
    }

    /**
     * Helper for adding routes with less code.
     * $name format should be in the following format:
     * * getList@/product/getList - GET method for controller action named getList()
     * * remove@/product/remove/{id} - GET & POST methods for controller action named remove($id)
     *
     *
     * For Example 'remove@/product/remove/{id}', is similar to this:
     *
     * $routes->add('remove@/product/remove/{id}', '/product/remove/{id}')
     * ->controller([static::getControllerClassName(), 'remove'])
     * ->methods(['GET','POST']);
     *
     * @param RoutingConfigurator $routes
     * @param string $name
     *
     * @return RouteConfigurator
     */
    public static function addRoute(RoutingConfigurator $routes, string $name)
    {
        $nameParts = explode('@/', $name);

        $action = $nameParts[0];
        $path = '/' . $nameParts[1];

        $methods = ['GET', 'POST'];

        if (substr($action, 0, 3) === 'get')
            $methods = ['GET'];
        elseif (substr($action, 0, 4) === 'post')
            $methods = ['POST'];

        return $routes->add($name, $path)
            ->controller([static::getControllerClassName(), $action])
            ->methods($methods);
    }

}