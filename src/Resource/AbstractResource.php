<?php


namespace Copper\Resource;


use Copper\ArrayReader;
use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBSeed;
use Copper\Entity\AbstractEntity;
use Symfony\Component\Routing\Loader\Configurator\RouteConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

abstract class AbstractResource
{
    private static $model = null;

    const PATH_GROUP = 'abstract_collection_resource';

    const GET_LIST = 'getList@/' . self::PATH_GROUP . '/list';
    const GET_EDIT = 'getEdit@/' . self::PATH_GROUP . '/edit/{id}';
    const POST_UPDATE = 'postUpdate@/' . self::PATH_GROUP . '/update/{id}';
    const GET_NEW = 'getNew@/' . self::PATH_GROUP . '/new';
    const POST_CREATE = 'postCreate@/' . self::PATH_GROUP . '/create';
    const POST_REMOVE = 'postRemove@/' . self::PATH_GROUP . '/remove/{id}';
    const POST_UNDO_REMOVE = 'postUndoRemove@/' . self::PATH_GROUP . '/remove/undo/{id}';

    /**
     * @return string
     */
    abstract static function getModelClassName();

    /**
     * @return string
     */
    abstract static function getEntityClassName();

    /**
     * @return string|false
     */
    static function getControllerClassName()
    {
        return false;
    }

    /**
     * @return string|false
     */
    static function getServiceClassName()
    {
        return false;
    }

    /**
     * @return string|false
     */
    static function getSeedClassName()
    {
        return false;
    }

    static function registerRoutes(RoutingConfigurator $routes)
    {
        return $routes;
    }

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
    static function getModel()
    {
        if (self::$model !== null)
            return self::$model;

        $className = static::getModelClassName();

        return self::$model = new $className();
    }

    static function getName()
    {
        return ArrayReader::lastValue(explode('\\', static::class));
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