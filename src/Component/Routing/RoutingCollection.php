<?php


namespace Copper\Component\Routing;


use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RoutingCollection
 * @package Copper\Component\Routing
 */
class RoutingCollection extends RouteCollection
{
    /**
     * @param ResourceInterface $resource
     */
    public function addResource($resource)
    {
        // this method should be empty - this is hack for symfony/config dependency removal
        // Especially Symfony\Component\Config\Resource\ResourceInterface
    }
}