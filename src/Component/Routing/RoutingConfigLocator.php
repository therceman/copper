<?php


namespace Copper\Component\Routing;


use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocatorInterface;

class RoutingConfigLocator implements FileLocatorInterface
{
    protected $path;

    /**
     * @param string $path A path where to look for resource
     */
    public function __construct($path)
    {
        $this->path =$path;
    }

    /**
     * {@inheritdoc}
     */
    public function locate($name, $currentPath = null, $first = true)
    {
       return $this->path.DIRECTORY_SEPARATOR.$name;

    }

}