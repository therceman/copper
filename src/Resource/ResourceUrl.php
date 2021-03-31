<?php


namespace Copper\Resource;


use Copper\Handler\ArrayHandler;
use Copper\Kernel;

class ResourceUrl
{
    protected $arguments;
    protected $route;
    protected $params;

    public function __construct($route, $arguments)
    {
        $this->route = $route;
        $this->arguments = $arguments;
    }

    /**
     * @param string $route
     * @param array $arguments
     *
     * @return ResourceUrl
     */
    public static function create(string $route, array $arguments)
    {
        return new self($route, $arguments);
    }

    /**
     * @param array $params
     *
     * @return ResourceUrl
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function toString()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        if ($this->params !== null)
            $parameters = ArrayHandler::merge($this->params, $this->arguments);
        else
            $parameters = $this->arguments;

        return Kernel::getRouteUrl($this->route, $parameters);
    }

}