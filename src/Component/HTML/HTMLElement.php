<?php


namespace Copper\Component\HTML;


use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;
use Copper\Sanitizer;
use DOMDocument;

/**
 * Class HTMLElement
 *
 * @package Copper\Component\HTML
 */
class HTMLElement
{
    const ATTR_STYLE = 'style';
    const ATTR_CLASS = 'class';
    const ATTR_NAME = 'name';
    const ATTR_ID = 'id';
    const ATTR_DISABLED = 'disabled';

    // start tag only / self closing

    const META = 'meta';
    const INPUT = 'input';
    const IMG = 'img';
    const HR = 'hr';
    const SOURCE = 'source';

    // with end tag

    const SCRIPT = 'script';
    const TITLE = 'title';
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
    const H1 = 'h1';
    const H2 = 'h2';
    const H3 = 'h3';
    const H4 = 'h4';
    const H5 = 'h5';
    const H6 = 'h6';
    const STYLE = 'style';
    const TABLE = 'table';
    const PICTURE = 'picture';

    /** @var string */
    private $tag;
    /** @var array */
    private $attributes;
    /** @var string */
    private $innerHTML;
    /** @var string */
    private $innerText;
    /** @var bool */
    private $hasEndTag;
    /** @var bool */
    private $toggled;

    /** @var HTMLElement[] */
    private $innerElements;

    private $attrValueDefaultDelimiter;
    private $sanitizer;

    /** @var HTMLElement|false */
    private $elementAfter;
    /** @var HTMLElement|false */
    private $elementBefore;

    /** @var HTMLElement|false */
    private $innerElementAfter;
    /** @var HTMLElement|false */
    private $innerElementBefore;

    /**
     * HTMLElement constructor.
     *
     * @param string $tag
     * @param bool $hasEndTag
     */
    public function __construct(string $tag, $hasEndTag = true)
    {
        $this->tag = $tag;
        $this->hasEndTag = $hasEndTag;
        $this->innerHTML = null;
        $this->innerText = null;
        $this->innerElements = new HTMLGroup();

        $this->attrValueDefaultDelimiter = ' ';
        $this->sanitizer = new Sanitizer();

        $this->elementAfter = false;
        $this->elementBefore = false;

        $this->initAttributes();
    }

    /**
     * HTMLElement static constructor
     *
     * @param string $tag
     * @param bool $hasEndTag
     */
    public static function new(string $tag, $hasEndTag = true)
    {
        return new static($tag, $hasEndTag);
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

    /**
     * @return bool
     */
    private function isAttributeListEmpty()
    {
        $empty = true;

        foreach ($this->attributes as $key => $value) {
            if (VarHandler::isArray($value) && count($value) > 0)
                $empty = false;

            if (VarHandler::isArray($value) === false && $value !== false)
                $empty = false;
        }

        return $empty;
    }

    /**
     * @param array $attrList
     *
     * @return string
     */
    private function createAttrString(array $attrList)
    {
        $strList = [];

        foreach ($attrList as $key => $value) {
            $valueStr = VarHandler::isArray($value) ? join($this->attrValueDefaultDelimiter, $value) : $value;

            if ($key === self::ATTR_STYLE)
                $valueStr = $this->createStyleString($value);

            if ($key === self::ATTR_CLASS)
                $valueStr = $this->createClassString($value);

            if ($value === false || $value === true)
                $valueStr = '="' . (($value === false) ? '0' : '1') . '"';
            else
                $valueStr = '="' . $this->sanitizer->double_quote_escape($valueStr) . '"';

            if (VarHandler::isArray($value) && count($value) === 0)
                continue;

            $strList[] = $this->sanitizer->tag_attr_escape($key) . $valueStr;
        }

        return join(' ', $strList);
    }

    /**
     * Toggle element on/off. If off - element won't be rendered.
     *
     * @param bool|null $state
     *
     * @return HTMLElement
     */
    public function toggle($state = true)
    {
        if ($state === null)
            $this->toggled = false;
        else
            $this->toggled = boolval($state);

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

    /**
     * @param string $attr
     *
     * @return $this
     */
    public function removeAttr(string $attr)
    {
        if (array_key_exists($attr, $this->attributes))
            unset($this->attributes[$attr]);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function data(string $key, $value)
    {
        $this->setAttr('data-' . $this->sanitizer->tag_attr_escape($key), $value);

        return $this;
    }

    /**
     * @param array|iterable $attrs
     * @return $this
     */
    public function setAttrs($attrs)
    {
        foreach ($attrs as $key => $value) {
            $this->setAttr($key, $value);
        }

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

        if (array_key_exists($attr, $this->attributes) && VarHandler::isArray($this->attributes[$attr])) {
            $this->attributes[$attr] = VarHandler::isArray($value) ? $value : [$value];
        } else {
            $this->attributes[$attr] = $value;
        }

        return $this;
    }

    /**
     * @param bool $state
     *
     * @return $this
     */
    public function disabled($state = true)
    {
        $this->setAttr(self::ATTR_DISABLED, ($state === true) ? false : null);

        return $this;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
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

        if (VarHandler::isArray($value) === false)
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
    public function hasClass(string $class)
    {
        return ArrayHandler::hasValue($this->attributes[self::ATTR_CLASS], $class);
    }

    /**
     * @param $value
     *
     * @return $this
     */
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
     * Set style using raw CSS statement
     * <hr>
     * <code>
     * - styleRaw('float:right;margin-bottom:10px')
     * </code>
     * @param string $style
     */
    public function styleRaw(string $style)
    {
        $keyValueList = StringHandler::split($style, ';');

        foreach ($keyValueList as $keyValue) {
            $res = StringHandler::split($keyValue, ':');

            if (count($res) !== 2)
                continue;

            $this->addStyle(StringHandler::trim($res[0]), StringHandler::trim($res[1]));
        }

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
            if (VarHandler::isArray($keyValuePair))
                foreach ($keyValuePair as $key => $value) {
                    $styles[$key] = $value;
                }
            else
                $styles[$k] = $keyValuePair;
        }

        $this->setAttr(self::ATTR_STYLE, $styles);

        return $this;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
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

    /**
     * @param string|null $id
     *
     * @return $this
     */
    public function id($id)
    {
        $this->setAttr(self::ATTR_ID, $id);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->getAttr(self::ATTR_ID);
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->getAttr(self::ATTR_NAME);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getAttr(string $key)
    {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null;
    }

    /**
     * @return string
     */
    public function getOuterHTML()
    {
        return $this->__toString();
    }

    /**
     * Outputs HTML tree as formatted XML string
     * 
     * @param string $version
     * @param string $encoding
     * @param bool $format
     * @param false $preserveWhiteSpace
     * @return false|string
     */
    public function toXML($version = '1.0', $encoding = 'UTF-8', $format = true, $preserveWhiteSpace = false)
    {
        $version = StringHandler::replace($version, '"', '');
        $encoding = StringHandler::replace($encoding, '"', '');

        $doc = new DOMDocument($version, $encoding);
        $doc->preserveWhiteSpace = $preserveWhiteSpace;
        $doc->formatOutput = $format;

        $doc->loadXML('<?xml version="' . $version . '" encoding="' . $encoding . '" ?>' . $this->getOuterHTML());

        return $doc->saveXML();
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function innerHTML($value)
    {
        if ($value !== null)
            $this->innerHTML = $value;

        return $this;
    }

    /**
     * @param string|null $text
     *
     * @return $this
     */
    public function innerText($text)
    {
        $this->innerText = $this->sanitizer->html_escape($text);

        return $this;
    }

    /**
     * @param HTMLElement $el
     *
     * @return $this
     */
    public function addElement(HTMLElement $el)
    {
        $this->innerElements->add($el);

        return $this;
    }

    /**
     * @return HTMLElement[]
     */
    public function getInnerElements()
    {
        return $this->innerElements->getList();
    }

    /**
     * @param $selector
     * @param \Closure $closure
     *
     * @return HTMLElement
     */
    public function findElement($selector, \Closure $closure)
    {
        $this->innerElements->findElement($selector, $closure);

        return $this;
    }

    /**
     * @param string $selector CSS Selector. E.g. ".icon", "#nav", "img"
     * @param \Closure $closure Callback with element. E.g. function($el) { ... }
     *
     * @return HTMLElement
     */
    public function findAllElements(string $selector, \Closure $closure)
    {
        $this->innerElements->findAllElements($selector, $closure);

        return $this;
    }

    /**
     * @param HTMLElementGroup $elGroup
     * @param bool $skipWrapper
     *
     * @return $this
     */
    public function addElementGroup(HTMLElementGroup $elGroup, $skipWrapper = false)
    {
        if ($skipWrapper === false) {
            $elList = $elGroup->getHTMLElements();
        } else {
            $elGroup->build();
            $elList = $elGroup->getList();
        }

        foreach ($elList as $el) {
            $this->addElement($el);
        }

        return $this;
    }

    /**
     * @param HTMLElement $el
     *
     * @return $this
     */
    public function addInnerElementBefore(HTMLElement $el)
    {
        $this->innerElementBefore = $el;

        return $this;
    }

    /**
     * @param HTMLElement $el
     *
     * @return $this
     */
    public function addInnerElementAfter(HTMLElement $el)
    {
        $this->innerElementAfter = $el;

        return $this;
    }

    /**
     * @param HTMLElement $el
     *
     * @return $this
     */
    public function addElementAfter(HTMLElement $el)
    {
        $this->elementAfter = $el;

        return $this;
    }

    /**
     * @param HTMLElement $el
     *
     * @return $this
     */
    public function addElementBefore(HTMLElement $el)
    {
        $this->elementBefore = $el;

        return $this;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param bool $newLineAfter
     *
     * @return string
     */
    public function getStartTag($newLineAfter = true)
    {
        $attrStr = $this->isAttributeListEmpty() ? '' : ' ' . $this->createAttrString($this->attributes);
        $tagStr = $this->sanitizer->tag_name_escape($this->tag);

        return '<' . $tagStr . $attrStr . '>' . (($newLineAfter) ? PHP_EOL : '');
    }

    /**
     * @param bool $newLineBefore
     *
     * @return string
     */
    public function getEndTag($newLineBefore = true)
    {
        $tagStr = $this->sanitizer->tag_name_escape($this->tag);

        return (($newLineBefore) ? PHP_EOL : '') . '</' . $tagStr . '>';
    }

    /**
     * @return string
     */
    public function getHTML()
    {
        if (count($this->innerElements->getList()) > 0) {
            $html = ArrayHandler::join($this->innerElements->getList(), PHP_EOL);
        } elseif ($this->innerHTML !== null) {
            $html = (strpos($this->innerHTML, PHP_EOL) !== false) ? $this->innerHTML . PHP_EOL : $this->innerHTML;
        } elseif ($this->innerText !== null) {
            $html = $this->innerText;
        } else {
            $html = '';
        }

        return $this->innerElementBefore . $html . $this->innerElementAfter;
    }

    /**
     * @return string
     */
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

        if ($this->elementBefore !== false)
            $tagStr = $this->elementBefore . $tagStr;

        if ($this->elementAfter !== false)
            $tagStr = $tagStr . $this->elementAfter;

        return $tagStr;
    }

    /**
     * @return $this
     */
    public function idAsName()
    {
        $this->id($this->getName());

        return $this;
    }

    /**
     * @return $this
     */
    public function title($title)
    {
        $this->setAttr('title', $title);

        return $this;
    }

    /**
     * @param string $js Javascript code
     *
     * @return $this
     */
    public function onClick(string $js)
    {
        $this->setAttr('onclick', $js);

        return $this;
    }

    /**
     * @param string $js Javascript code
     *
     * @return $this
     */
    public function onChange(string $js)
    {
        $this->setAttr('onchange', $js);

        return $this;
    }

    /**
     * @param string $js Javascript code
     *
     * @return $this
     */
    public function onInput(string $js)
    {
        $this->setAttr('oninput', $js);

        return $this;
    }
}