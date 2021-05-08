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

use Copper\Handler\FileHandler;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * PhpFileLoader loads routes from a PHP file.
 *
 * The file must return a RouteCollection instance.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RoutingConfigLoader extends PhpFileLoader
{
    public function locate($file)
    {
        return FileHandler::pathFromArray([FileHandler::projectPathFromArray(['config']), $file]);
    }

    /**
     * Loads a PHP file.
     *
     * @param string      $file A PHP file path
     * @param string|null $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     */
    public function load($file, $type = null)
    {
        $path = $this->locate($file);

        $load = \Closure::bind(function ($file) {
            return include $file;
        }, null, new class {
            // anonymous class
        });

        $result = $load($path);

        if ($result instanceof \Closure) {
            $collection = new RouteCollection();
            $result(new \Copper\Component\Routing\RoutingConfigurator($collection));
        } else {
            $collection = $result;
        }

        $collection->addResource(new FileResource($path));

        return $collection;
    }

}
