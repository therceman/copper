<?php


namespace Copper\Component\HTML;


use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;

abstract class HTMLElementGroup
{
    /** @var HTMLElement */
    private $wrapper = null;

    /** @var HTMLElement[] */
    protected $list;
    protected $toggle = true;

    abstract public function build();

    /**
     * HTMLElementGroup constructor.
     *
     * @param array $array
     */
    public function __construct($array = [])
    {
        $this->list = $array;
    }

    /**
     * @param string $class
     * @param string|null $id
     *
     * @return HTMLElementGroup
     */
    public function divWrapper(string $class, $id = null)
    {
        $this->wrapper = HTML::div($class)->id($id);

        return $this;
    }

    /**
     * @return HTMLElement|null
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * @param HTMLElement|null $wrapper
     *
     * @return HTMLElementGroup
     */
    public function wrapper($wrapper)
    {
        $this->wrapper = $wrapper;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function toggle(bool $bool)
    {
        $this->toggle = $bool;

        return $this;
    }

    /**
     * @param HTMLElement $el
     *
     * @return HTMLElementGroup
     */
    public function add(HTMLElement $el)
    {
        $this->list[] = $el;

        return $this;
    }

    /**
     * @return HTMLElementGroup
     */
    protected function clearList()
    {
        $this->list = [];

        return $this;
    }

    /**
     * @return HTMLElement[]
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param string $selector
     * @param \Closure $closure
     * @param bool $stopOnFirstMatch
     *
     * @return $this
     */
    private function findElementsUsingSelector(string $selector, \Closure $closure, $stopOnFirstMatch = false)
    {
        $firstMatchFound = false;

        foreach ($this->list as $key => $el) {
            $matchedEl = null;

            // ------ Class Selector ------

            $selectorClassList = ArrayHandler::map(StringHandler::regexAll($selector, '/\.([a-zA-Z0-9_-]*)/ms'),
                function ($match) {
                    return $match[1];
                });

            if (count($selectorClassList) > 0 && ArrayHandler::hasValueList($el->getClassList(), $selectorClassList))
                $matchedEl = $el;

            // ------ ID Selector ------

            $selectorIdList = StringHandler::regexAll($selector, '/\#([a-zA-Z0-9_-]*)/ms');

            if (count($selectorIdList) > 0 && $el->getId() === $selectorIdList[0][1])
                $matchedEl = $el;

            // ------ Tag Selector

            if ($el->getTag() === $selector)
                $matchedEl = $el;

            if ($stopOnFirstMatch && $firstMatchFound)
                continue;

            if ($matchedEl !== null) {
                $firstMatchFound = true;
                $this->list[$key] = $closure($el);
            }
        }

        return $this;
    }

    /**
     * @param string $selector
     * @param \Closure $closure
     *
     * @return HTMLElementGroup
     */
    public function findElement(string $selector, \Closure $closure)
    {
        return $this->findElementsUsingSelector($selector, $closure, true);
    }

    /**
     * @param string $selector
     * @param \Closure $closure
     *
     * @return HTMLElementGroup
     */
    public function findAllElements(string $selector, \Closure $closure)
    {
        return $this->findElementsUsingSelector($selector, $closure, false);
    }

    /**
     * @param string $tag
     *
     * @return HTMLElement|null
     */
    public function getElement(string $tag)
    {
        $foundEl = null;

        foreach ($this->list as $el) {
            if ($el->getTag() === $tag)
                $foundEl = $el;
        }

        return $foundEl;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }


    /**
     * @return HTMLElement[]
     */
    public function getHTMLElements()
    {
        $this->build();

        $el_list = $this->getList();

        if ($this->wrapper !== null) {
            return [$this->wrapper->innerHTML(join(PHP_EOL, $el_list))];
        }

        return $el_list;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->toggle === false)
            return '';

        $this->build();

        if ($this->wrapper !== null)
            return $this->wrapper->innerHTML(join(PHP_EOL, $this->list))->getOuterHTML();

        return join(PHP_EOL, $this->list);
    }
}