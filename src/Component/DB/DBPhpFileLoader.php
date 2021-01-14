<?php


namespace Copper\Component\DB;


use Copper\PhpFileLoader;

class DBPhpFileLoader extends PhpFileLoader
{
    protected function getConfiguratorInstance()
    {
        return new DBConfigurator();
    }

    protected function getProtectedFileLoaderClassName()
    {
        return ProtectedDBPhpFileLoader::class;
    }
}

/**
 * @internal
 */
final class ProtectedDBPhpFileLoader extends DBPhpFileLoader
{
}