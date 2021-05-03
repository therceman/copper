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
     */
    public function __construct(string $configFilename)
    {
        $this->config = $this->configure(CPConfigurator::class, $configFilename);
    }

}