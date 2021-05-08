<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Copper\Component\Routing;


use Symfony\Component\Routing\Loader\PhpFileLoader;

/**
 * PhpFileLoader loads routes from a PHP file.
 *
 * The file must return a RouteCollection instance.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RoutingConfigLoader extends PhpFileLoader
{

    /**
     * Loads a PHP file.
     *
     *
     * @return RouteCollection A RouteCollection instance
     */
    public function load($path, $type = null)
    {
        $load = \Closure::bind(function ($file) {
            return include $file;
        }, null, new class {
            // anonymous class
        });

        $result = $load($path);

        if ($result instanceof \Closure) {
            $collection = new RouteCollection();
            $result(new RoutingConfigurator($collection));
        } else {
            $collection = $result;
        }

        $collection->addResource($path);

        print_r($collection);

        return $collection;
    }

}
