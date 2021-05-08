<?php


namespace Copper\Component\Routing;


use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Routing\Loader\Configurator\Traits\AddTrait;
use Symfony\Component\Routing\RouteCollection;

class RoutingConfigurator
{
    use AddTrait;

    public function __construct(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param string $name
     * @return CollectionConfigurator
     */
    final public function collection(string $name = '')
    {
        return new CollectionConfigurator($this->collection, $name);
    }
}