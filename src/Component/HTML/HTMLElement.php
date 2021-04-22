<?php


namespace Copper\Component\HTML;


use Copper\Handler\ArrayHandler;
use Copper\Sanitizer;

class HTMLElement
{
    const ATTR_STYLE = 'style';
    const ATTR_CLASS = 'class';
    const ATTR_NAME = 'name';
    const ATTR_ID = 'id';
    const ATTR_DISABLED = 'disabled';

    // start tag only / self closing

    const INPUT = 'input';
    const IMG = 'img';

    // with end tag

    const SVG = 'svg';
    const DIV = 'div';
    const A = 'a';
    const SPAN = 'span';
    const P = 'p';
    const LABEL = 'label';
    const OPTION = 'option';
    const SELECT = 'select';
    const BUTTON = 'button';
    const TD = 'td';
    const TR = 'tr';
    const UL = 'ul';
    const LI = 'li';
    const TH = 'th';
    const OBJECT = 'object';
    const TEXTAREA = 'textarea';
    const FORM = 'form';

    /** @var string */
    private $tag;
    /** @var array */
    private $attributes;
    /** @var string */
    private $innerHTML;
    /** @var bool */
    private $hasEndTag;
    /** @var bool */
    private $toggled;

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

    public function __construct(string $tag, $hasEndTag = true)
    {
        $this->tag = $tag;
        $this->hasEndTag = $hasEndTag;
        $this->innerHTML = false;

        $this->attrValueDefaultDelimiter = ' ';
        $this->sanitizer = new Sanitizer();

        $this->afterHTML = false;
        $this->beforeHTML = false;

        $this->initAttributes();
    }

    /**
     * @param string $attrValueDefaultDelimiter
     */
    public function setAttrValueDefaultDelimiter(string $attrValueDefaultDelimiter)
    {
        $this->attrValueDefaultDelimiter = $attrValueDefaultDelimiter;
    }

    /**
     * @param string $tag
     *
     * @return bool
     */
    public static function hasEndTag(string $tag)
    {
        return (ArrayHandler::hasValue([self::INPUT, self::IMG], $tag) === false);
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
     * Toggle element on/off. If off - element won't be rendered.
     *
     * @param bool $force
     */
    public function toggle($force = true)
    {
        $this->toggled = boolval($force);

        return $this;
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

    public function disabled($state = true)
    {
        $this->setAttr(self::ATTR_DISABLED, ($state === true) ? false : null);

        return $this;
    }

    public function name($name)
    {
        $this->setAttr(self::ATTR_NAME, $name);

        return $this;
    }

    /**
     * @param string|array $value
     *
     * @return $this
     */
    public function class($value)
    {
        if ($value === null)
            return $this;

        if (is_array($value) === false)
            $value = explode(' ', $value);

        $this->setAttr(self::ATTR_CLASS, $value);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClassList()
    {
        return $this->attributes[self::ATTR_CLASS];
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function hasClass($class)
    {
        return ArrayHandler::hasValue($this->attributes[self::ATTR_CLASS], $class);
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
     * @param string $value
     * @param bool $force
     *
     * @return HTMLElement
     */
    public function toggleClass(string $value, $force = true)
    {
        if (ArrayHandler::hasValue($this->attributes[self::ATTR_CLASS], $value))
            $this->deleteClass($value);

        if (boolval($force))
            $this->addClass($value);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function addClass(string $value)
    {
        if ($value === null)
            return $this->deleteClass($value);

        $this->attributes[self::ATTR_CLASS][] = $value;

        return $this;
    }

    /**
     * Set style
     *
     * @param array $arr - [["display" => "block"], ["color":"red"]] or ["display" => "block"]
     *
     * @return $this
     */
    public function style(array $arr)
    {
        $styles = [];

        foreach ($arr as $k => $keyValuePair) {
            if (is_array($keyValuePair))
                foreach ($keyValuePair as $key => $value) {
                    $styles[$key] = $value;
                }
            else
                $styles[$k] = $keyValuePair;
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
        return $this->findAttribute(self::ATTR_ID);
    }

    public function getName()
    {
        return $this->findAttribute(self::ATTR_NAME);
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

    /**
     * @param HTMLElementGroup $elGroup
     * @return $this
     */
    public function addElementGroup(HTMLElementGroup $elGroup)
    {
        foreach ($elGroup->getList() as $el)
            $this->addElement($el);

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
        if ($this->toggled === false)
            return '';

        if ($this->hasEndTag === false) {
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

    function onClick($js)
    {
        $this->setAttr('onclick', $js);

        return $this;
    }

    function onChange($js)
    {
        $this->setAttr('onchange', $js);

        return $this;
    }

    function onInput($js)
    {
        $this->setAttr('oninput', $js);

        return $this;
    }
}