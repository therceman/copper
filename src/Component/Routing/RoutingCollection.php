<?php


namespace Copper\Component\Routing;


class RouteCollection extends \Symfony\Component\Routing\RouteCollection
{
    public function addResource(FileRe$resource)
    {
        // this method should be empty - this is hack for symfony/config dependency removal
        // Especially Symfony\Component\Config\Resource\ResourceInterface
    }
}