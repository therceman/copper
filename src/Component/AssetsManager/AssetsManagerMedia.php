<?php


namespace Copper\Component\AssetsManager;


use Copper\Handler\FileHandler;
use Copper\Handler\NumberHandler;
use Copper\Handler\VarHandler;

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
    private $width;
    private $height;
    private $mimeType;
    private $filesize;

    /**
     * AssetsManagerMedia constructor.
     * @param $relFolderPath
     * @param $filename
     */
    public function __construct($relFolderPath, $filename)
    {
        $this->filename = $filename;
        $this->relFolderPath = $relFolderPath;
    }

    /**
     * Create from relative folder path + filename
     *
     * @param $relFolderPath
     * @param $filename
     * @return AssetsManagerMedia
     */
    public static function create($relFolderPath, $filename)
    {
        $media = new self($relFolderPath, $filename);

        $media->process();

        return $media;
    }

    /**
     * Create from relative filepath
     *
     * @param string|array $relFilepath
     * @return AssetsManagerMedia
     */
    public static function createFromRelPath($relFilepath)
    {
        if (VarHandler::isArray($relFilepath))
            $relFilepath = FileHandler::pathFromArray($relFilepath);

        $relDirPath = FileHandler::getFolderName($relFilepath);
        $filename = FileHandler::getFilename($relFilepath);

        return self::create($relDirPath, $filename);
    }

    /**
     * @param $absFilepath
     * @return AssetsManagerMedia
     */
    public static function createFromAbsPath($absFilepath)
    {
        $absDirPath = FileHandler::getFolderName($absFilepath);
        $filename = FileHandler::getFilename($absFilepath);

        $media = new self(null, $filename);

        $media->absoluteFilepath = $absFilepath;
        $media->absFolderPath = $absDirPath;

        $media->relativeFilepath = null;
        $media->relFolderPath = null;

        $media->processImageData();

        return $media;
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

        $this->process();

        return $this;
    }

    public function getFilename()
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

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getMimeType()
    {
        return $this->mimeType;
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
            'rel_path' => $this->getRelativeFilepath(),
            'filename' => $this->getFilename(),
            'filesize' => $this->getFilesize(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'mime_type' => $this->getMimeType(),
        ];
    }

    /**
     * @return $this
     */
    public function processImageData()
    {
        if (FileHandler::fileExists($this->absoluteFilepath) === false)
            return $this;

        $imageSizeInfo = getimagesize($this->absoluteFilepath);

        $this->width = $imageSizeInfo[0] ?? null;
        $this->height = $imageSizeInfo[1] ?? null;
        $this->mimeType = $imageSizeInfo['mime'] ?? null;

        $this->filesize = $this->getFilesize(true, 2);

        return $this;
    }

    private function process()
    {
        $this->relativeFilepath = FileHandler::pathFromArray([$this->relFolderPath, $this->filename]);
        $this->absFolderPath = AssetsManager::getMediaFolderPath([$this->relFolderPath]);
        $this->absoluteFilepath = FileHandler::pathFromArray([$this->absFolderPath, $this->filename]);

        $this->processImageData();
    }

    /**
     * @return $this
     */
    public function buildPath()
    {
        FileHandler::createFolder($this->absFolderPath, true);

        return $this;
    }

}