<?php


namespace Copper\Component\Routing;


class RouteCollection extends \Symfony\Component\Routing\RouteCollection
{
    public function addResource($resource)
    {
        // this is hack for not using Symfony\Component\Config\Resource\ResourceInterface
    }
}