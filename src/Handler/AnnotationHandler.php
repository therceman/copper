<?php


namespace Copper\Handler;


class AnnotationHandler
{
    /**
     * Get Class Property Type Name
     *
     * @param string $className
     * @param string $propertyName
     *
     * @return string|null
     */
    public static function getTypeName(string $className, string $propertyName)
    {
        try {
            $rp = new \ReflectionProperty($className, $propertyName);
        } catch (\ReflectionException $e) {
            return null;
        }

        if (preg_match('/@var\s+([^\s]+)/', $rp->getDocComment(), $matches)) {
            return $matches[1];
        }

        return null;
    }
}