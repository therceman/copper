<?php


namespace Copper\Component\Routing;


use Copper\Handler\ArrayHandler;
use Copper\Handler\FileHandler;
use Copper\Kernel;
use Copper\Resource\AbstractResource;

/**
 * Class RoutingConfigLoader
 * @package Copper\Component\Routing
 */
class RoutingConfigLoader
{
    private $configFolder;
    private $configFile;

    private $packageConfig;
    private $appConfig;
    private $appResourceFolder;

    /**
     * RoutingConfigLoader constructor.
     * @param string $configFolder
     * @param string $configFile
     * @param string $appResourceFolder
     */
    public function __construct(string $configFolder, string $configFile, string $appResourceFolder)
    {
        $this->configFolder = $configFolder;
        $this->configFile = $configFile;

        $this->packageConfig = Kernel::getPackagePath([$configFolder, $configFile]);
        $this->appConfig = Kernel::getAppPath([$configFolder, $configFile]);

        $this->appResourceFolder = $appResourceFolder;
    }

    /**
     * @return RoutingCollection
     */
    private function loadPackageRoutes()
    {
        return $this->load($this->packageConfig);
    }

    /**
     * @param RoutingCollection $routes
     */
    private function loadResourceRoutes(RoutingCollection $routes)
    {
        $resourceFiles = FileHandler::getFilesInFolder($this->appResourceFolder);

        if ($resourceFiles->hasError())
            return;

        foreach ($resourceFiles->result as $key => $resourceFile) {
            $filePath = FileHandler::pathFromArray([$this->appResourceFolder, $resourceFile]);

            /** @var AbstractResource $resourceClass */
            $resourceClass = FileHandler::getFileClassName($filePath);

            if (ArrayHandler::hasValue(get_class_methods($resourceClass), 'registerRoutes') === false)
                continue;

            $collection = new RoutingCollection();

            $resourceClass::registerRoutes(new RoutingConfigurator($collection));

            $collection->addResource(null);

            $routes->addCollection($collection);
        }
    }

    /**
     * @param RoutingCollection $routes
     */
    private function loadAppRoutes(RoutingCollection $routes)
    {
        if (FileHandler::fileExists(Kernel::getAppPath($this->configFolder) === false))
            return;

        if (FileHandler::fileExists($this->appConfig) === false)
            return;

        $collection = $this->load($this->appConfig);

        $routes->addCollection($collection);
    }

    /**
     * @return RoutingCollection
     */
    public function loadRoutes()
    {
        $routes = $this->loadPackageRoutes();

        $this->loadResourceRoutes($routes);
        $this->loadAppRoutes($routes);

        return $routes;
    }

    /**
     * Loads route collection from PHP file
     *
     * @param string $path
     *
     * @return RoutingCollection A RoutingCollection instance
     */
    private function load(string $path)
    {
        $load = \Closure::bind(function ($file) {
            return include $file;
        }, null, new class {
            // anonymous class
        });

        $result = $load($path);

        if ($result instanceof \Closure) {
            $collection = new RoutingCollection();
            $result(new RoutingConfigurator($collection));
        } else {
            $collection = $result;
        }

        $collection->addResource(null);

        return $collection;
    }

}
