<?php


namespace Copper\Traits;


use Copper\Handler\FileHandler;
use Copper\Kernel;
use Copper\PhpFileLoader;
use Symfony\Component\Config\FileLocator;

trait ComponentHandlerTrait
{
    protected function mergeConfig($packageConfig, $projectConfig)
    {
        if ($projectConfig === null)
            return $packageConfig;

        $vars = get_object_vars($projectConfig);

        foreach ($vars as $key => $value) {
            if ($value !== null || trim($value) !== "")
                $packageConfig->$key = $value;
        }

        return $packageConfig;
    }

    protected function configure($configurator, $configFilename)
    {
        $packagePath = Kernel::getPackagePath() . '/' . Kernel::CONFIG_FOLDER;
        $projectPath = Kernel::getProjectPath() . '/' . Kernel::CONFIG_FOLDER;

        $loader = PhpFileLoader::create($configurator, new FileLocator($packagePath));
        $packageConfig = $loader->load($configFilename);

        $projectConfig = null;
        if (FileHandler::fileExists($projectPath . '/' . $configFilename)) {
            $loader = PhpFileLoader::create($configurator, new FileLocator($projectPath));
            $projectConfig = $loader->load($configFilename);
        }

        return $this->mergeConfig($packageConfig, $projectConfig);
    }
}