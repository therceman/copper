<?php


namespace Copper;


use Copper\Handler\FileHandler;
use Symfony\Component\Config\FileLocatorInterface;

class ConfigLoader
{
    private $configurator;
    private $configFolderPath;

    public function __construct($configFolderPath)
    {
        $this->configFolderPath = $configFolderPath;
    }

    public static function create($configuratorClass, $configFolderPath)
    {
        $instance = new static($configFolderPath);

        $instance->configurator = new $configuratorClass();

        return $instance;
    }

    public function locate($file)
    {
        return FileHandler::pathFromArray([$this->configFolderPath, $file]);
    }

    /**
     * Loads a PHP Config file and returns configured class
     *
     * @param string $file A PHP file path
     * @param string|null $type The resource type
     *
     * @return mixed
     */
    public function load(string $file, $type = null)
    {
        $path = $this->locate($file);

        $load = \Closure::bind(function ($file) {
            return include $file;
        }, null, new class {
            // anonymous class
        });

        $result = $load($path);

        $configurator = $this->configurator;

        if ($result instanceof \Closure)
            $result($configurator, $this);

        return $configurator;
    }

}