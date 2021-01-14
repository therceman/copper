<?php


namespace Copper\Component\CP;


use Copper\PhpFileLoader;

class CPPhpFileLoader extends PhpFileLoader
{
    protected function getConfiguratorInstance()
    {
        return new CPConfigurator();
    }

    protected function getProtectedFileLoaderClassName()
    {
        return ProtectedCPPhpFileLoader::class;
    }
}

/**
 * @internal
 */
final class ProtectedCPPhpFileLoader extends CPPhpFileLoader
{
}