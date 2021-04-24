<?php


namespace Copper;


class App
{
    /** @var AppConfigurator */
    public $config;

    /**
     * App constructor.
     *
     * @param AppConfigurator $projectConfig
     * @param AppConfigurator $packageConfig
     */
    public function __construct(AppConfigurator $packageConfig, AppConfigurator $projectConfig = null)
    {
        $this->config = $this->mergeConfig($packageConfig, $projectConfig);
    }

    private function mergeConfig(AppConfigurator $packageConfig, AppConfigurator $projectConfig = null)
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
}