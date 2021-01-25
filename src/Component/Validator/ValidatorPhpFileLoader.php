<?php


namespace Copper\Component\Validator;


use Copper\PhpFileLoader;

class ValidatorPhpFileLoader extends PhpFileLoader
{
    protected function getConfiguratorInstance()
    {
        return new ValidatorConfigurator();
    }

    protected function getProtectedFileLoaderClassName()
    {
        return ProtectedValidatorPhpFileLoader::class;
    }
}

/**
 * @internal
 */
final class ProtectedValidatorPhpFileLoader extends ValidatorPhpFileLoader
{
}