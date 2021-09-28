<?php


namespace Copper\Component\AssetsManager;


use Copper\Handler\FileHandler;
use Copper\Handler\NumberHandler;

/**
 * Class AssetsManagerMedia
 * @package Copper\Component\AssetsManager
 */
class AssetsManagerMedia
{
    public const JPG = 'jpg';
    public const JPEG = 'jpeg';
    public const PNG = 'png';
    public const WEBP = 'webp';

    public const SUPPORTED_FORMAT_LIST = [
        self::JPEG,
        self::JPG,
        self::PNG,
        self::WEBP
    ];

    private $relativeFilepath;
    private $absoluteFilepath;

    private $absFolderPath;
    private $relFolderPath;
    private $filename;

    /**
     * AssetsManagerMedia constructor.
     * @param $relFolderPath
     * @param $filename
     */
    public function __construct($relFolderPath, $filename)
    {
        $this->filename = $filename;
        $this->relFolderPath = $relFolderPath;

        $this->build();
    }

    /**
     * @param $relFolderPath
     * @param $filename
     * @return AssetsManagerMedia
     */
    public static function create($relFolderPath, $filename)
    {
        return new self($relFolderPath, $filename);
    }

    /**
     * @return $this
     */
    public function setExtensionJPG()
    {
        return $this->setExtension(self::JPG);
    }

    /**
     * @return $this
     */
    public function setExtensionWEBP()
    {
        return $this->setExtension(self::WEBP);
    }

    /**
     * @return $this
     */
    public function setExtensionPNG()
    {
        return $this->setExtension(self::PNG);
    }

    /**
     * @param string $newExtension
     * @return $this
     */
    private function setExtension(string $newExtension)
    {
        $this->filename = FileHandler::setExtension($this->filename, $newExtension);

        $this->build();

        return $this;
    }

    private function getFilename()
    {
        return $this->filename;
    }

    private function getAbsFolderPath()
    {
        return $this->absFolderPath;
    }

    private function getRelFolderPath()
    {
        return $this->relFolderPath;
    }

    public function getAbsoluteFilepath()
    {
        return $this->absoluteFilepath;
    }

    /**
     * Returns filesize in kilobytes
     * @param bool $round
     * @param int $roundDecimal
     * @return false|int
     */
    public function getFilesize($round = true, $roundDecimal = 0)
    {
        $filesize = FileHandler::getFilesize($this->absoluteFilepath) / 1000;

        return ($round) ? NumberHandler::round($filesize, $roundDecimal) : $filesize;
    }

    public function getRelativeFilepath()
    {
        return $this->relativeFilepath;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return AssetsManager::media_src($this->relativeFilepath);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'url' => $this->getUrl(),
            'path' => $this->getRelativeFilepath(),
            'filename' => $this->getFilename(),
            'filesize' => $this->getFilesize(),
        ];
    }

    private function build()
    {
        $this->relativeFilepath = FileHandler::pathFromArray([$this->relFolderPath, $this->filename]);

        $this->absFolderPath = AssetsManager::getMediaFolderPath([$this->relFolderPath]);

        FileHandler::createFolder($this->absFolderPath, true);

        $this->absoluteFilepath = FileHandler::pathFromArray([$this->absFolderPath, $this->filename]);
    }

}