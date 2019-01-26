<?php

namespace Copper;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

trait RequestTrait
{
    /**
     * @param string $message The response message
     * @param int $status The response status code
     *
     * @return Response
     */
    public function error($message, $status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return new Response('<b>Error</b>: ' . $message, $status);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $name The name of the route
     * @param array $parameters An array of parameters
     * @param int $type The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     */
    public function generateRouteUrl($name, $parameters = [], $type = UrlGenerator::ABSOLUTE_PATH)
    {
        if (isset($this->routes) && isset($this->requestContext)) {
            $generator = new UrlGenerator($this->routes, $this->requestContext);

            return $generator->generate($name, $parameters, $type);
        } else {
            return $this->error('Parent class is missing RouteCollection, RequestContext instances');
        }
    }

    /**
     * Returns the absolute URL (with scheme and host) for the given route.
     * If schemeRelative is enabled, it'll create a scheme-relative URL
     *
     * @param $name
     * @param array $parameters
     * @param bool $schemeRelative
     *
     * @return string
     */
    public function url($name, $parameters = [], $schemeRelative = false)
    {
        $type = ($schemeRelative) ? UrlGenerator::NETWORK_PATH : UrlGenerator::ABSOLUTE_URL;

        return $this->generateRouteUrl($name, $parameters, $type);
    }

    /**
     * Returns the relative URL (without the scheme and host) for the given route.
     * If relative is enabled, it'll create a path relative to the current path
     *
     * @param $name
     * @param array $parameters
     * @param bool $relative
     *
     * @return string
     */
    public function path($name, $parameters = [], $relative = false)
    {
        $type = ($relative) ? UrlGenerator::RELATIVE_PATH : UrlGenerator::ABSOLUTE_PATH;

        return $this->generateRouteUrl($name, $parameters, $type);
    }
}