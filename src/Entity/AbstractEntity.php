<?php

namespace Copper\Entity;

use Copper\Component\Templating\ViewHandler;
use Copper\Handler\AnnotationHandler;
use Copper\Traits\EntityStateFields;
use Symfony\Component\HttpFoundation\ParameterBag;

class AbstractEntity
{
    /** @var int */
    public $id;

    /**
     * @param ViewHandler $view
     * @param string $key
     *
     * @return static|null
     */
    public static function fromView(ViewHandler $view, string $key)
    {
        return $view->dataBag->get($key, null);
    }

    /**
     * @param ViewHandler $view
     * @param string $key
     *
     * @return static[]|array
     */
    public static function fromViewAsList(ViewHandler $view, string $key)
    {
        return  $view->dataBag->get($key, []);
    }

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