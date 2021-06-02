<?php


namespace Copper\Component\HTML;


use Copper\Handler\ArrayHandler;
use Copper\Handler\VarHandler;

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
     * @param HTMLElement[]|HTMLElement $elements
     * @return array
     */
    private function addElements($elements)
    {
        $element_list = [];

        if (VarHandler::isArray($elements) === false)
            $elements = [$elements];

        foreach ($elements as $element) {
            if (VarHandler::isArray($element))
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

    public function attrs($attrs) {
        $this->attrs = $attrs;

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
            return $this->setWrapper(null);

        $wrapper = new HTMLElement($this->tag, HTMLElement::hasEndTag($this->tag));
        $wrapper->class($this->class);
        $wrapper->id($this->id);
        $wrapper->setAttrs($this->wrapper_attrs);

        $this->setWrapper($wrapper);

        return $this;
    }
}