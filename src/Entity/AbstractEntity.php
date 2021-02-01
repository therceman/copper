<?php

namespace Copper\Entity;

use Copper\AnnotationReader;

class AbstractEntity
{
    /** @var int */
    public $id;

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
            if (property_exists($self, $key)) {
                // TODO Class Property Types should be cached somehow for better performance
                $type = AnnotationReader::getTypeName(static::class, $key);

                if ($value !== null)
                    settype($value, $type);

                $self->$key = $value;
            }
        }

        return $self;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return (array)$this;
    }

    public function exists()
    {
        return ($this->id !== null);
    }
}