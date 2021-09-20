<?php

namespace Copper\Entity;

use Copper\Component\Templating\ViewHandler;
use Copper\Handler\AnnotationHandler;
use Copper\Handler\ArrayHandler;
use Copper\Traits\EntityStateFields;
use Symfony\Component\HttpFoundation\ParameterBag;

class AbstractEntity
{
    /** @var int */
    public $id;

    public static function new()
    {
        return new static();
    }

    /**
     * @param ViewHandler $view
     * @param string $key
     * @param mixed|null $default
     *
     * @return static|null
     */
    public static function fromView(ViewHandler $view, string $key, $default = null)
    {
        return $view->dataBag->get($key, $default);
    }

    /**
     * @param ViewHandler $view
     * @param string $key
     *
     * @return static[]|array
     */
    public static function fromViewAsList(ViewHandler $view, string $key)
    {
        return $view->dataBag->get($key, []);
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

                if ($value !== null && $type !== null)
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

    /**
     * @return array
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * @param string|null $key
     *
     * @return bool
     */
    public function has($key)
    {
        if ($key === null)
            return false;

        return property_exists($this, $key);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function set(string $key, $value)
    {
        if (property_exists($this, $key))
            $this->$key = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param null $default
     *
     * @return null
     */
    public function get(string $key, $default = null)
    {
        if (property_exists($this, $key))
            return $this->$key;

        return $default;
    }

    /**
     * Returns null for undefined/deleted values
     *
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
        return null;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function delete(string $key)
    {
        if (property_exists($this, $key))
            unset($this->$key);

        return $this;
    }

    public function getFields()
    {
        return ArrayHandler::keyList(get_class_vars(static::class));
    }

    public function exists()
    {
        return ($this->id !== null);
    }

    public function hasStateFields()
    {
        return array_key_exists(EntityStateFields::class, class_uses($this));
    }

    public function isArchived()
    {
        return (property_exists($this, 'archived_at') && $this->archived_at !== null);
    }
}