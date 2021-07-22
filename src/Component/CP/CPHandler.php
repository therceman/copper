<?php

namespace Copper\Component\CP;

use Copper\Traits\ComponentHandlerTrait;

class CPHandler
{
    use ComponentHandlerTrait;

    /** @var CPConfigurator */
    public $config;

    /**
     * CPHandler constructor.
     *
     * @param string $configFilename
     * @param CPConfigurator|null $config
     */
    public function __construct(string $configFilename, CPConfigurator $config = null)
    {
        $this->config = $config ?? $this->configure(CPConfigurator::class, $configFilename);
    }

}