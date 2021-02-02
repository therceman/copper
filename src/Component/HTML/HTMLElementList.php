<?php


namespace Copper\Component\HTML;


class HTMLElementList
{
    /** @var HTMLElement[] */
    private $list;

    public function __construct()
    {
        $this->list = [];
    }

    /**
     * @param HTMLElement $el
     */
    public function add(HTMLElement $el)
    {
        $this->list[] = $el;
    }

    public function hasAttrValue($attrKey, $attrValue)
    {
        return ($this->findByAttrValue($attrKey, $attrValue) !== null);
    }

    public function hasId($id)
    {
        return ($this->findByAttrValue('id', $id) !== null);
    }

    public function hasName($name)
    {
        return ($this->findByAttrValue('name', $name) !== null);
    }

    /**
     * @param string $attrKey
     * @param string $attrVal
     *
     * @return HTMLElement|null
     */
    public function findByAttrValue(string $attrKey, string $attrVal)
    {
        $foundEl = null;

        foreach ($this->list as $el) {
            $attr = $el->findAttribute($attrKey);

            if ($attr !== null && $attr === $attrVal)
                $foundEl = $el;
        }

        return $foundEl;
    }

    /**
     * @param string $tag
     *
     * @return bool
     */
    public function hasTag(string $tag)
    {
        return ($this->findByTag($tag) !== null);
    }

    /**
     * @param string $tag
     *
     * @return HTMLElement|null
     */
    public function findByTag(string $tag)
    {
        $foundEl = null;

        foreach ($this->list as $el) {
            if ($el->getTag() === $tag)
                $foundEl = $el;
        }

        return $foundEl;
    }

    /**
     * @param $id
     *
     * @return HTMLElement|null
     */
    public function findById($id)
    {
        return $this->findByAttrValue('id', $id);
    }

    /**
     * @param $name
     *
     * @return HTMLElement|null
     */
    public function findByName($name)
    {
        return $this->findByAttrValue('name', $name);
    }

    public function __toString()
    {
        return join(PHP_EOL, $this->list);
    }
}