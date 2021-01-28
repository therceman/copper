<?php


namespace Copper\Resource;


use Symfony\Component\Routing\Loader\Configurator\RouteConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

abstract class AbstractResource
{
    abstract static function getControllerClassName();

    abstract static function getModelClassName();

    abstract static function getEntityClassName();

    abstract static function getServiceClassName();

    abstract static function registerRoutes(RoutingConfigurator $routes);

    static function getSeedClassName()
    {
        // provide Seed Class Name if exists
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
    public static function addRouteHelper(RoutingConfigurator $routes, string $name)
    {
        $nameParts = explode('@/', $name);

        $action = $nameParts[0];
        $path = '/' . $nameParts[1];

        $methods = ['GET', 'POST'];

        if (substr($action, 0, 3) === 'get')
            $methods = ['GET'];
        elseif (substr($action, 0, 3) === 'post')
            $methods = ['POST'];

        return $routes->add($name, $path)
            ->controller([static::getControllerClassName(), $action])
            ->methods($methods);
    }

}