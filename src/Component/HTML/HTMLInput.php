<?php


namespace Copper\Component\HTML;


class HTMLInput extends HTMLElement
{
    const ATTR_VALUE = 'value';
    const ATTR_PLACEHOLDER = 'placeholder';
    const ATTR_AUTOFOCUS = 'autofocus';
    const ATTR_SPELLCHECK = 'spellcheck';
    const ATTR_AUTOCOMPLETE = 'autocomplete';
    const ATTR_TYPE = 'type';

    public function __construct()
    {
        parent::__construct(HTMLElement::INPUT, false);
    }

    public function value($value)
    {
        $this->setAttr(self::ATTR_VALUE, $value);

        return $this;
    }

    public function type($type)
    {
        $this->setAttr(self::ATTR_TYPE, $type);

        return $this;
    }

    public function placeholder($text)
    {
        $this->setAttr(self::ATTR_PLACEHOLDER, $text);

        return $this;
    }

    public function autofocus($enabled = true)
    {
        $this->setAttr(self::ATTR_AUTOFOCUS, ($enabled) ? true : null);

        return $this;
    }

    public function spellcheck($enabled = true)
    {
        $this->setAttr(self::ATTR_SPELLCHECK, ($enabled) ? null : 'false');

        return $this;
    }

    public function autocomplete($enabled = true)
    {
        $this->setAttr(self::ATTR_AUTOCOMPLETE, ($enabled) ? null : 'false_off');

        return $this;
    }
}