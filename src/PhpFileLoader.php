<?php


namespace Copper;


use Symfony\Component\Config\Loader\FileLoader;

abstract class PhpFileLoader extends FileLoader
{
    abstract protected function getProtectedFileLoaderClassName();

    abstract protected function getConfiguratorInstance();

    /**
     * Loads a PHP file.
     *
     * @param string $file A PHP file path
     * @param string|null $type The resource type
     *
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $this->setCurrentDir(dirname($path));

        // the closure forbids access to the private scope in the included file
        $loader = $this;
        $load = \Closure::bind(function ($file) use ($loader) {
            return include $file;
        }, null, $this->getProtectedFileLoaderClassName());

        $result = $load($path);

        $config = $this->getConfiguratorInstance();

        if ($result instanceof \Closure)
            $result($config, $this);

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'php' === $type);
    }
}