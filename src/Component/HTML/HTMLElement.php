<?php


namespace Copper\Component\HTML;


use Copper\Sanitizer;

class HTMLElement
{
    const ATTR_STYLE = 'style';
    const ATTR_CLASS = 'class';
    const ATTR_NAME = 'name';
    const ATTR_ID = 'id';
    const ATTR_DISABLED = 'disabled';

    /** @var string */
    private $tag;
    /** @var array */
    private $attributes;
    /** @var string */
    private $innerHTML;
    /** @var boolean */
    private $selfClosing;

    private $attrValueDefaultDelimiter;
    private $sanitizer;

    /** @var HTMLElement|false */
    private $afterHTML;
    /** @var HTMLElement|false */
    private $beforeHTML;

    /** @var HTMLElement|false */
    private $innerAfterHTML;
    /** @var HTMLElement|false */
    private $innerBeforeHTML;

    public function __construct(string $tag, $selfClosing = false, $attrValueDefaultDelimiter = ' ')
    {
        $this->tag = $tag;
        $this->selfClosing = $selfClosing;
        $this->innerHTML = false;

        $this->attrValueDefaultDelimiter = $attrValueDefaultDelimiter;
        $this->sanitizer = new Sanitizer();

        $this->afterHTML = false;
        $this->beforeHTML = false;

        $this->initAttributes();
    }

    private function initAttributes()
    {
        $this->attributes = [
            self::ATTR_CLASS => [],
            self::ATTR_STYLE => [],
        ];
    }

    private function isAttributeListEmpty()
    {
        $empty = true;

        foreach ($this->attributes as $key => $value) {
            if (is_array($value) && count($value) > 0)
                $empty = false;

            if (is_array($value) === false && $value !== false)
                $empty = false;
        }

        return $empty;
    }

    private function createAttrString(array $attrList)
    {
        $strList = [];

        foreach ($attrList as $key => $value) {
            $valueStr = is_array($value) ? join($this->attrValueDefaultDelimiter, $value) : $value;

            if ($key === self::ATTR_STYLE)
                $valueStr = $this->createStyleString($value);

            if ($key === self::ATTR_CLASS)
                $valueStr = $this->createClassString($value);

            if ($value === false || $value === true)
                $valueStr = '="' . (($value === false) ? '0' : '1') . '"';
            else
                $valueStr = '="' . $this->sanitizer->double_quote_escape($valueStr) . '"';

            if (is_array($value) && count($value) === 0)
                continue;

            $strList[] = $this->sanitizer->key_escape($key) . $valueStr;
        }

        return join(' ', $strList);
    }

    /**
     * @param array $classList
     *
     * @return string
     */
    private function createClassString(array $classList)
    {
        return join(' ', $classList);
    }

    /**
     * @param array $styleList
     *
     * @return string
     */
    private function createStyleString(array $styleList)
    {
        $strList = [];

        foreach ($styleList as $key => $value) {
            $strList[] = $key . ': ' . $value;
        }

        return join('; ', $strList);
    }

    public function removeAttr(string $attr)
    {
        if (array_key_exists($attr, $this->attributes))
            unset($this->attributes[$attr]);

        return $this;
    }

    /**
     * @param string $attr
     * @param string|array $value
     *
     * @return $this
     */
    public function setAttr(string $attr, $value)
    {
        if ($value === null)
            return $this->removeAttr($attr);

        if (array_key_exists($attr, $this->attributes) && is_array($this->attributes[$attr])) {
            $this->attributes[$attr] = is_array($value) ? $value : [$value];
        } else {
            $this->attributes[$attr] = $value;
        }

        return $this;
    }

    public function disabled()
    {
        $this->setAttr(self::ATTR_DISABLED, false);

        return $this;
    }

    public function name($name)
    {
        $this->setAttr(self::ATTR_NAME, $name);

        return $this;
    }

    /**
     * @param array|string $value
     *
     * @return $this
     */
    public function class($value)
    {
        $this->setAttr(self::ATTR_CLASS, $value);

        return $this;
    }

    public function deleteClass($value)
    {
        foreach ($this->attributes[self::ATTR_CLASS] as $key => $val) {
            if ($val === $value)
                unset($this->attributes[self::ATTR_CLASS][$key]);
        }

        return $this;
    }

    /**
     * @param array|string $value
     *
     * @return $this
     */
    public function addClass($value)
    {
        if ($value === null)
            return $this->deleteClass($value);

        $this->attributes[self::ATTR_CLASS][] = $value;

        return $this;
    }

    /**
     * @param array $arr - [["display" => "block"], ["color":"red"]]
     *
     * @return $this
     */
    public function style(array $arr)
    {
        $styles = [];

        foreach ($arr as $keyValuePair) {
            foreach ($keyValuePair as $key => $value) {
                $styles[$key] = $value;
            }
        }

        $this->setAttr(self::ATTR_STYLE, $styles);

        return $this;
    }

    public function deleteStyle(string $key)
    {
        if (array_key_exists($key, $this->attributes[self::ATTR_STYLE]))
            unset($this->attributes[self::ATTR_STYLE][$key]);

        return $this;
    }

    /**
     * @param string $key
     * @param array|string $value
     *
     * @return $this
     */
    public function addStyle(string $key, $value)
    {
        if ($value === null)
            return $this->deleteStyle($key);

        $this->attributes[self::ATTR_STYLE][$key] = $value;

        return $this;
    }

    public function id($id)
    {
        $this->setAttr(self::ATTR_ID, $id);

        return $this;
    }

    public function getId()
    {
        return $this->attributes[self::ATTR_ID];
    }

    public function getName()
    {
        return $this->attributes[self::ATTR_NAME];
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function findAttribute($key)
    {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null;
    }

    public function getOuterHTML()
    {
        return $this->__toString();
    }

    public function innerHTML($value)
    {
        $this->innerHTML = $value;

        return $this;
    }

    public function innerText($text)
    {
        $this->innerHTML = $this->sanitizer->html_escape($text);

        return $this;
    }

    public function addElement(HTMLElement $el)
    {
        $this->innerHTML = $this->innerHTML . PHP_EOL . $el;

        return $this;
    }

    public function addInnerElementBefore(HTMLElement $el)
    {
        $this->innerBeforeHTML = $el;

        return $this;
    }

    public function addInnerElementAfter(HTMLElement $el)
    {
        $this->innerAfterHTML = $el;

        return $this;
    }

    public function addElementAfter(HTMLElement $el)
    {
        $this->afterHTML = $el;

        return $this;
    }

    public function addElementBefore(HTMLElement $el)
    {
        $this->beforeHTML = $el;

        return $this;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getStartTag($newLineAfter = true)
    {
        $attrStr = $this->isAttributeListEmpty() ? '' : ' ' . $this->createAttrString($this->attributes);
        $tagStr = $this->sanitizer->key_escape($this->tag);

        return '<' . $tagStr . $attrStr . '>' . (($newLineAfter) ? PHP_EOL : '');
    }

    public function getEndTag($newLineBefore = true)
    {
        $tagStr = $this->sanitizer->key_escape($this->tag);

        return (($newLineBefore) ? PHP_EOL : '') . '</' . $tagStr . '>';
    }

    public function getHTML()
    {
        $html = (strpos($this->innerHTML, PHP_EOL) !== false) ? $this->innerHTML . PHP_EOL : $this->innerHTML;

        return $this->innerBeforeHTML . $html . $this->innerAfterHTML;
    }

    public function __toString()
    {
        if ($this->selfClosing) {
            $tagStr = $this->getStartTag(false);
        } else {
            $tagStr = $this->getStartTag(false)
                . $this->getHTML()
                . $this->getEndTag(false);
        }

        if ($this->beforeHTML !== false)
            $tagStr = $this->beforeHTML . $tagStr;

        if ($this->afterHTML !== false)
            $tagStr = $tagStr . $this->afterHTML;

        return $tagStr;
    }

    function idAsName()
    {
        $this->id($this->getName());

        return $this;
    }

}