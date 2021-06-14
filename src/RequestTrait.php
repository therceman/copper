<?php

namespace Copper;

use Copper\Handler\VarHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

trait RequestTrait
{
    /**
     * Throws error as response and appends it to error.log
     *
     * @param string $message The response message
     * @param int $status The response status code
     *
     * @return Response
     */
    public function error(string $message, $status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return Kernel::getErrorHandler()->throwErrorAsResponse($message, $status);
    }

    /**
     * @param string $message
     * @param array|object|string|int|float|bool|null $data
     * @param int $status
     */
    public function logError(string $message, $data = null, $status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        Kernel::logError($message, $data, $status);
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
        return Kernel::getRouteRelativePath($name, $parameters);
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
        return Kernel::getRouteNetworkPath($name, $parameters);
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
    public function url($name, $parameters = [], $withScheme = true)
    {
        return Kernel::getRouteUrl($name, $parameters, $withScheme);
    }

    /**
     * Returns current URL (with/without query)
     *
     * @param bool $withQuery
     * @return string
     */
    public function currentUrl($withQuery = true)
    {
        return ($withQuery) ? $this->request_uri : explode('?', $this->request_uri)[0];
    }
}