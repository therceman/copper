<?php


namespace Copper;


use Copper\PhpFileLoader;

class AppPhpFileLoader extends PhpFileLoader
{
    protected function getConfiguratorInstance()
    {
        return new AppConfigurator();
    }

    protected function getProtectedFileLoaderClassName()
    {
        return ProtectedAppPhpFileLoader::class;
    }
}

/**
 * @internal
 */
final class ProtectedAppPhpFileLoader extends AppPhpFileLoader
{
}