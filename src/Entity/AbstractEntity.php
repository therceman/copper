<?php

namespace Copper\Entity;

use Copper\Handler\AnnotationHandler;
use Copper\Traits\EntityStateFields;

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
                $type = AnnotationHandler::getTypeName(static::class, $key);

                if ($value === '')
                    $value = null;

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

    public function hasStateFields()
    {
        return array_key_exists(EntityStateFields::class, class_uses($this));
    }

    public function isRemoved()
    {
        return ($this->hasStateFields() && $this->removed_at !== null);
    }
}