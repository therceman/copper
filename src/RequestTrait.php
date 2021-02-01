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
     * Returns a path relative to the current path, e.g. "../parent-file".
     *
     * @param $name
     * @param array $parameters
     * @return string
     */
    public function relativePath($name, $parameters = [])
    {
        return $this->generateRouteUrl($name, $parameters, UrlGenerator::RELATIVE_PATH);
    }

    /**
     * Returns a scheme-relative URL for the given route, e.g. "//example.com/dir/file".
     *
     * @param $name
     * @param array $parameters
     * @return string
     */
    public function networkPath($name, $parameters = [])
    {
        return $this->generateRouteUrl($name, $parameters, UrlGenerator::NETWORK_PATH);
    }

    /**
     * Returns the URL (without the scheme and host) for the given route.
     * If withScheme is enabled, it'll create the URL (with scheme and host) for the given route.
     *
     * @param $name
     * @param array $parameters
     * @param bool $withScheme
     *
     * @return string
     */
    public function url($name, $parameters = [], $withScheme = false)
    {
        $type = ($withScheme) ? UrlGenerator::ABSOLUTE_URL : UrlGenerator::ABSOLUTE_PATH;

        return $this->generateRouteUrl($name, $parameters, $type);
    }
}