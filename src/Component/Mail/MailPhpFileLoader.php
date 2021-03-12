<?php


namespace Copper\Component\Mail;


use Copper\PhpFileLoader;

class MailPhpFileLoader extends PhpFileLoader
{
    protected function getConfiguratorInstance()
    {
        return new MailConfigurator();
    }

    protected function getProtectedFileLoaderClassName()
    {
        return ProtectedMailPhpFileLoader::class;
    }
}

/**
 * @internal
 */
final class ProtectedMailPhpFileLoader extends MailPhpFileLoader
{
}