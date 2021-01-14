<?php

namespace Copper\Component\CP;

class CPHandler
{
    /** @var CPConfigurator */
    public $config;

    /**
     * CPHandler constructor.
     *
     * @param CPConfigurator $projectConfig
     * @param CPConfigurator $packageConfig
     */
    public function __construct(CPConfigurator $packageConfig, CPConfigurator $projectConfig = null)
    {
        $this->config = $this->mergeConfig($packageConfig, $projectConfig);
    }

    private function mergeConfig(CPConfigurator $packageConfig, CPConfigurator $projectConfig = null)
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