<?php


namespace Copper\Component\AssetsManager;


use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;
use Copper\Handler\FileHandler;
use Copper\Handler\StringHandler;
use Copper\Kernel;
use Copper\Traits\ComponentHandlerTrait;

/**
 * Class AssetsManager
 * @package Copper\Component\AssetsManager
 */
class AssetsManager
{
    use ComponentHandlerTrait;

    const ERROR_VERSION_FILE_READ_FAILED = 'AM_VFRF';

    // ---- core folders -----

    const SRC_JS_PATH = '/src_js/';

    const APP_ENTITY_JS = '/src_js/Entity';
    const APP_MODEL_JS = '/src_js/Model';

    const APP_PUBLIC_ENTITY_JS = '/js/Entity';
    const APP_PUBLIC_MODEL_JS = '/js/Model';

    // ---- public content folders -----

    const JS_PATH = '/js/';
    const CSS_PATH = '/css/';
    const MEDIA_PATH = '/media/';

    const VERSION_FILENAME = '.version';

    const VERSION_SEPARATOR = '?';

    private static $version;

    public static $core_js_files = [];
    public static $app_entity_js_files = [];
    public static $app_model_js_files = [];
    public static $public_entity_js_files = [];
    public static $public_model_js_files = [];
    public static $app_js_files = [];

    /** @var AssetsManagerConfigurator */
    public $config;

    /**
     * ValidatorHandler constructor.
     *
     * @param string $configFilename
     */
    public function __construct(string $configFilename = Kernel::VALIDATOR_CONFIG_FILE)
    {
        $this->config = $this->configure(AssetsManagerConfigurator::class, $configFilename);
    }

    // ---------------------- INIT ----------------------

    public static function init()
    {
        $res_core = FileHandler::getFilesInFolder(Kernel::getPackagePath(self::SRC_JS_PATH), true);
        $res_app = FileHandler::getFilesInFolder(Kernel::getAppPublicPath(self::JS_PATH));

        $res_entity_app = FileHandler::getFilesInFolder(Kernel::getAppPath(self::APP_ENTITY_JS), true);
        $res_model_app = FileHandler::getFilesInFolder(Kernel::getAppPath(self::APP_MODEL_JS), true);

        $res_entity_public_path = Kernel::getAppPublicPath(self::APP_PUBLIC_ENTITY_JS);
        $res_model_public_path = Kernel::getAppPublicPath(self::APP_PUBLIC_MODEL_JS);

        FileHandler::createFolder(Kernel::getAppPublicPath(self::APP_PUBLIC_ENTITY_JS));
        FileHandler::createFolder(Kernel::getAppPublicPath(self::APP_PUBLIC_MODEL_JS));

        $res_entity_public = FileHandler::getFilesInFolder($res_entity_public_path);
        $res_model_public = FileHandler::getFilesInFolder($res_model_public_path);

        if ($res_core->isOK())
            self::$core_js_files = $res_core->result;

        if ($res_app->isOK())
            self::$app_js_files = $res_app->result;

        if ($res_entity_app->isOK())
            self::$app_entity_js_files = $res_entity_app->result;

        if ($res_model_app->isOK())
            self::$app_model_js_files = $res_model_app->result;

        if ($res_entity_public->isOK())
            self::$public_entity_js_files = $res_entity_public->result;

        if ($res_model_public->isOK())
            self::$public_model_js_files = $res_model_public->result;
    }

    // ---------------------- PRIVATE ----------------------

    /**
     * @return string
     */
    private static function getVersionFromGit()
    {
        return exec('git rev-parse --short HEAD');
    }

    /**
     * @param $version
     * @param $git_index_mod_time
     * @return FunctionResponse
     */
    private static function saveVersion($version, $git_index_mod_time)
    {
        $infoFilePath = FileHandler::appPathFromArray([self::VERSION_FILENAME]);

        return FileHandler::setContent($infoFilePath, json_encode([
            'git_index_mod_time' => $git_index_mod_time,
            'version' => $version
        ]));
    }

    private static function filepath($folder, $file)
    {
        $filepath = $folder . $file;

        return $filepath . self::VERSION_SEPARATOR . self::version();
    }

    private static function process_js_src($file, $src_js_files, $trg_js_files, $srcFilePath, $trgFileFolderPath)
    {
        $res = new FunctionResponse();

        if (ArrayHandler::hasValue($src_js_files, $file) === false)
            return $res->fail('skip', $file);

        $mod_time_data = ArrayHandler::findKey($src_js_files, $file);

        $mod_time = StringHandler::split($mod_time_data, '_')[0];

        $coreFile = ["name" => $file, "mod_time" => $mod_time];
        $newAppFileName = StringHandler::replace($file, '.js', '.' . $coreFile['mod_time'] . '.js');

        $appFileSearchResult = ArrayHandler::findFirstByRegex($trg_js_files, '/.*(.\d{10,}).js/');

        if ($appFileSearchResult === null || $appFileSearchResult !== $newAppFileName) {
            FileHandler::copyFileToFolder($srcFilePath, $trgFileFolderPath, $newAppFileName);

            if ($appFileSearchResult !== null && $appFileSearchResult !== $newAppFileName)
                FileHandler::delete(FileHandler::pathFromArray([$trgFileFolderPath, $appFileSearchResult]));
        }

        return $res->result($newAppFileName);
    }

    private static function process_core_js_src($file)
    {
        $srcFilePath = Kernel::getPackagePath([self::SRC_JS_PATH, $file]);
        $trgFileFolderPath = Kernel::getAppPublicPath(self::JS_PATH);

        return self::process_js_src($file, self::$core_js_files, self::$app_js_files, $srcFilePath, $trgFileFolderPath)->result;
    }

    private static function process_app_js_src($file)
    {
        $fileParts = StringHandler::split($file, '/');

        $map = [
            'Entity' => [self::$app_entity_js_files, self::$public_entity_js_files],
            'Model' => [self::$app_model_js_files, self::$public_model_js_files],
        ];

        if (ArrayHandler::count($fileParts) > 1 && ArrayHandler::hasValue(ArrayHandler::keyList($map), $fileParts[0])) {
            $folder = $fileParts[0];

            $file = StringHandler::replace($file, $folder . '/', '');

            $srcFilePath = Kernel::getAppPath([self::SRC_JS_PATH, $folder, $file]);
            $trgFileFolderPath = Kernel::getAppPublicPath([self::JS_PATH, $folder]);

            $out_file = self::process_js_src($file, $map[$folder][0], $map[$folder][1], $srcFilePath, $trgFileFolderPath);

            return $out_file->hasError() ? $out_file->result : FileHandler::pathFromArray([$folder, $out_file->result]);
        } else
            return $file;
    }

    // ---------------------- PUBLIC ----------------------

    public static function version()
    {
        if (self::$version !== null) {
            return self::$version;
        } else {
            $infoFilePath = FileHandler::appPathFromArray([self::VERSION_FILENAME]);

            $git_index_mod_time = filemtime(FileHandler::appPathFromArray(['.git', 'index']));

            if (FileHandler::fileExists($infoFilePath) === false) {
                self::$version = self::getVersionFromGit();
                self::saveVersion(self::$version, $git_index_mod_time);

                return self::$version;
            }

            $fileContent = FileHandler::getContent($infoFilePath);

            if ($fileContent->hasError()) {
                Kernel::logError(self::ERROR_VERSION_FILE_READ_FAILED, self::VERSION_FILENAME);

                self::$version = self::getVersionFromGit();

                return self::$version;
            }

            $fileContentResult = json_decode($fileContent->result, true);

            if ($fileContentResult['git_index_mod_time'] != $git_index_mod_time) {
                self::$version = self::getVersionFromGit();
                self::saveVersion(self::$version, $git_index_mod_time);
            } else {
                self::$version = $fileContentResult['version'];
            }
        }

        return self::$version;
    }

    public static function js_folder()
    {
        return Kernel::getAppPublicUri() . self::JS_PATH;
    }

    public static function css_folder()
    {
        return Kernel::getAppPublicUri() . self::CSS_PATH;
    }

    public static function media_folder()
    {
        return Kernel::getAppPublicUri() . self::MEDIA_PATH;
    }

    public static function js_src($file)
    {
        $file = self::process_core_js_src($file);
        $file = self::process_app_js_src($file);

        return self::filepath(self::js_folder(), $file);
    }

    public static function js($file)
    {
        return '<script src="' . self::js_src($file) . '"></script>';
    }

    public static function css_href($file)
    {
        return self::filepath(self::css_folder(), $file);
    }

    public static function css($file)
    {
        return '<link rel="stylesheet" href="' . self::css_href($file) . '"/>' . "\r\n";
    }

    private static function isFileExtensionInList($list, $filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return ArrayHandler::hasValue($list, $extension);
    }

    public static function getMediaFolder($file)
    {
        $config = Kernel::getAssetsManager()->config;

        $external_media_domain = $config->external_media_domain;
        $file_whitelisted = true;
        $file_blacklisted = false;

        if ($config->external_media_blacklist !== null)
            $file_blacklisted = self::isFileExtensionInList($config->external_media_blacklist, $file);

        if ($config->external_media_whitelist !== null)
            $file_whitelisted = self::isFileExtensionInList($config->external_media_whitelist, $file);

        if ($file_blacklisted)
            $file_whitelisted = false;

        if ($file_whitelisted)
            $file_blacklisted = false;

        if ($external_media_domain !== null && ($file_whitelisted || $file_blacklisted === false)) {
            return $external_media_domain;
        }

        return self::media_folder();
    }

    public static function media_src($file)
    {
        $media_folder = self::getMediaFolder($file);

        return self::filepath($media_folder, $file);
    }
}
