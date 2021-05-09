<?php


namespace Copper\Traits;


use Copper\Handler\FileHandler;
use Copper\Kernel;
use Copper\ConfigLoader;

trait ComponentHandlerTrait
{
    protected function mergeConfig($packageConfig, $appConfig)
    {
        if ($appConfig === null)
            return $packageConfig;

        $vars = get_object_vars($appConfig);

        foreach ($vars as $key => $value) {
            if ($value !== null || trim($value) !== "")
                $packageConfig->$key = $value;
        }

        return $packageConfig;
    }

    /**
     * @param string $configuratorClassName
     * @param string $configFilename
     *
     * @return mixed
     */
    private function loadPackageConfig(string $configuratorClassName, string $configFilename)
    {
        $packageConfigPath = Kernel::getPackagePath(Kernel::CONFIG_FOLDER);

        $loader = ConfigLoader::create($configuratorClassName, $packageConfigPath);

        return $loader->load($configFilename);
    }

    /**
     * @param string $configuratorClassName
     * @param string $configFilename
     *
     * @return mixed|null
     */
    private function loadAppConfig(string $configuratorClassName, string $configFilename)
    {
        $appConfigPath = Kernel::getAppPath(Kernel::CONFIG_FOLDER);

        if (FileHandler::fileExists($appConfigPath) === false)
            return null;

        if (FileHandler::fileExists(FileHandler::pathFromArray([$appConfigPath, $configFilename])) === false)
            return null;

        $loader = ConfigLoader::create($configuratorClassName, $appConfigPath);

        return $loader->load($configFilename);
    }

    protected function configure(string $configuratorClassName, string $configFilename)
    {
        $packageConfig = $this->loadPackageConfig($configuratorClassName, $configFilename);
        $appConfig = $this->loadAppConfig($configuratorClassName, $configFilename);

        return $this->mergeConfig($packageConfig, $appConfig);
    }
}