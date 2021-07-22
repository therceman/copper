<?php


namespace Copper;


use Copper\Traits\ComponentHandlerTrait;

class App
{
    use ComponentHandlerTrait;

    /** @var AppConfigurator */
    public $config;

    /**
     * App constructor.
     *
     * @param string $configFilename
     * @param AppConfigurator $configurator
     */
    public function __construct(string $configFilename, AppConfigurator $configurator = null)
    {
        $this->config = $configurator ?? $this->configure(AppConfigurator::class, $configFilename);

        if ($this->config->timezone !== false)
            date_default_timezone_set($this->config->timezone);
    }

}