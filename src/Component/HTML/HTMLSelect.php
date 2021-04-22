<?php


namespace Copper\Component\HTML;


class HTMLSelect extends HTMLElement
{
    private $useKeyAsValue = false;
    private $value = '';
    private $options = [];

    public function __construct()
    {
        parent::__construct(HTMLElement::SELECT);
    }

    public function options($options)
    {
        $this->options = $options;

        return $this;
    }

    public function value($val)
    {
        $this->value = $val;

        return $this;
    }

    public function useKeyAsValue($status)
    {
        $this->useKeyAsValue = $status;

        return $this;
    }

    public function __toString()
    {
        foreach ($this->options as $value => $text) {
            $optionEl = HTML::option($text, ($this->useKeyAsValue) ? $value : $text);

            if ($this->useKeyAsValue === false && $this->value == $text || $this->useKeyAsValue && $this->value == $value)
                $optionEl->setAttr('selected', true);

            $this->addElement($optionEl);
        }

        return parent::__toString();
    }
}