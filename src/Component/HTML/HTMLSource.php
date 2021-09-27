<?php


namespace Copper\Component\HTML;


use Copper\Handler\ArrayHandler;
use Copper\Handler\FileHandler;
use Copper\Handler\StringHandler;

class HTMLSource extends HTMLElement
{
    const ATTR_SRCSET = 'srcset';
    const ATTR_MEDIA = 'media';
    const ATTR_TYPE = 'type';

    public function __construct()
    {
        parent::__construct(HTMLElement::SOURCE, false);
    }

    public function srcset($value)
    {
        $this->setAttr(self::ATTR_SRCSET, $value);

        return $this;
    }

    public function media($value)
    {
        $this->setAttr(self::ATTR_MEDIA, $value);

        return $this;
    }

    public function type($value)
    {
        $this->setAttr(self::ATTR_TYPE, $value);

        return $this;
    }

    private function getSrcset()
    {
        return $this->getAttr(self::ATTR_SRCSET);
    }

    private function getType()
    {
        return $this->getAttr(self::ATTR_TYPE);
    }

    private function detectMimeTypeBySrc($srcset)
    {
        if ($srcset === null)
            return null;

        $url = StringHandler::split(StringHandler::split($srcset)[0], ' ')[0];
        $filename = FileHandler::getFilename($url);
        $extension = StringHandler::regex($filename, '/\.(\w{0,5})/m');

        return ArrayHandler::switch($extension,
            [
                'bmp', 'gif', 'ico', 'jpeg', 'jpg',
                'png', 'svg', 'tif', 'webp'
            ],
            [
                'image/bmp', 'image/gif', 'image/vnd.microsoft.icon', 'image/jpeg', 'image/jpeg',
                'image/png', 'image/svg+xml', 'image/tiff', 'image/webp'
            ]
        );
    }

    public function getStartTag($newLineAfter = true)
    {
        if ($this->getType() === null)
            $this->type($this->detectMimeTypeBySrc($this->getSrcset()));

        return parent::getStartTag($newLineAfter);
    }
}