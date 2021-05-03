<?php


namespace Copper;


use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;

class PhpFileLoader extends FileLoader
{
    private $configurator;

    public static function create($configuratorClass, FileLocatorInterface $fileLocator)
    {
        $instance = new static($fileLocator);

        $instance->configurator = new $configuratorClass();

        return $instance;
    }

    /**
     * Loads a PHP file.
     *
     * @param string $file A PHP file path
     * @param string|null $type The resource type
     *
     * @return mixed
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $this->setCurrentDir(dirname($path));

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

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'php' === $type);
    }
}