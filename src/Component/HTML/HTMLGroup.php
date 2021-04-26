<?php


namespace Copper\Component\HTML;


use Copper\Handler\ArrayHandler;

/**
 * Class HTMLGroup
 *
 * @package Copper\Component\HTML
 */
class HTMLGroup extends HTMLElementGroup
{
    private $class = null;
    private $tag = HTMLElement::DIV;
    private $id = null;
    private $useWrapper = true;
    private $elements = [];

    /**
     * HTMLGroup constructor.
     *
     * @param HTMLElement[] $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $this->addElements($elements);

        parent::__construct();
    }

    /**
     * @param HTMLElement[] $elements
     * @return array
     */
    private function addElements(array $elements)
    {
        $element_list = [];

        foreach ($elements as $element) {
            if (is_array($element))
                $element_list = ArrayHandler::merge($element_list, $this->addElements($element));
            elseif ($element !== null)
                $element_list[] = $element;
        }

        return $element_list;
    }

    /**
     * @param bool $bool
     *
     * @return HTMLGroup
     */
    public function useWrapper(bool $bool)
    {
        $this->useWrapper = $bool;

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return HTMLGroup
     */
    public function tag(string $tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @param string|null $class
     *
     * @return HTMLGroup
     */
    public function class($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param string|int|null $id
     *
     * @return HTMLGroup
     */
    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return HTMLGroup
     */
    public function build()
    {
        $this->list = [];

        foreach ($this->elements as $element) {
            $this->add($element);
        }

        if ($this->useWrapper === false)
            return $this->wrapper(null);

        $wrapper = new HTMLElement($this->tag, HTMLElement::hasEndTag($this->tag));
        $wrapper->class($this->class);
        $wrapper->id($this->id);

        $this->wrapper($wrapper);

        return $this;
    }
}