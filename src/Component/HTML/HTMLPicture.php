<?php


namespace Copper\Component\HTML;


use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;

class HTMLPicture extends HTMLElement
{
    private $imgAlt;
    private $imgClass;
    private $imgSrc;

    /** @var HTMLElement[] */
    private $sourceList;

    public function __construct($sourceList)
    {
        $this->sourceList = $sourceList;

        parent::__construct(HTMLElement::PICTURE, true);
    }

    public function imgAlt($value)
    {
        $this->imgAlt = $value;

        return $this;
    }

    public function imgClass($value)
    {
        $this->imgClass = $value;

        return $this;
    }

    public function imgSrc($value)
    {
        $this->imgSrc = $value;

        return $this;
    }

    public function getHTML()
    {
        foreach ($this->sourceList as $el) {
            $this->addElement($el);
        }

        if ($this->imgSrc === null) {
            $src = ArrayHandler::lastValue($this->sourceList)->getAttr('srcset');
            $src = StringHandler::split($src);
            $this->imgSrc = StringHandler::split($src[0],' ')[0];
        }

        $img = HTML::img($this->imgSrc, $this->imgAlt, $this->imgClass);

        $this->addElement($img);

        return parent::getHTML();
    }
}