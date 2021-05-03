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
     */
    public function __construct(string $configFilename)
    {
        $this->config = $this->configure(AppConfigurator::class, $configFilename);

        if ($this->config->timezone !== false)
            date_default_timezone_set($this->config->timezone);
    }

}