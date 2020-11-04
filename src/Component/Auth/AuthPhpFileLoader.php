<?php


namespace Copper\Component\Auth;


use Symfony\Component\Config\Loader\FileLoader;

class AuthPhpFileLoader extends FileLoader
{
    /**
     * Loads a PHP file.
     *
     * @param string      $file A PHP file path
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
        }, null, ProtectedAuthPhpFileLoader::class);

        $result = $load($path);

        $authConfig = new AuthConfigurator();

        if ($result instanceof \Closure)
            $result($authConfig, $this);

        return $authConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'php' === $type);
    }
}

/**
 * @internal
 */
final class ProtectedAuthPhpFileLoader extends AuthPhpFileLoader
{
}