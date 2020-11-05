<?php

namespace Copper\Entity;

class AbstractEntity
{
    /**
     * @param null|$array
     *
     * @return static
     */
    public static function fromArray($array)
    {
        if ($array === null)
            return null;

        $self = new static();

        foreach ($array as $key => $value) {
            if (property_exists($self, $key))
                $self->$key = $value;
        }

        return $self;
    }
}