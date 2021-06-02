<?php


namespace Copper\Component\AssetsManager;


use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;
use Copper\Handler\FileHandler;
use Copper\Handler\StringHandler;
use Copper\Kernel;

class AssetsManager
{
    // ---- core folders -----

    const CORE_JS = '/src_js/';

    // ---- public folders -----

    const JS_PATH = '/js/';
    const CSS_PATH = '/css/';
    const MEDIA_PATH = '/media/';

    const VERSION_FILENAME = '.version';

    const VERSION_SEPARATOR = '?';

    private static $version;

    public static $core_js_files = [];
    public static $app_js_files = [];

    // ---------------------- INIT ----------------------

    public static function init()
    {
        $res_core = FileHandler::getFilesInFolder(Kernel::getPackagePath(self::CORE_JS), true);
        $res_app = FileHandler::getFilesInFolder(Kernel::getAppPublicPath(self::JS_PATH));

        if ($res_core->isOK())
            self::$core_js_files = $res_core->result;

        if ($res_app->isOK())
            self::$app_js_files = $res_app->result;
    }

    // ---------------------- PRIVATE ----------------------

    private static function base_uri()
    {
        return Kernel::getBaseUri(false);
    }

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

    private static function process_core_js_src($file)
    {
        if (ArrayHandler::hasValue(self::$core_js_files, $file) === false)
            return $file;

        $coreFile = ["name" => $file, "mod_time" => ArrayHandler::findKey(self::$core_js_files, $file)];
        $newAppFileName = StringHandler::replace($file, '.js', '.' . $coreFile['mod_time'] . '.js');

        $appFileSearchResult = ArrayHandler::findFirstByRegex(self::$app_js_files, '/.*(.\d{10,}).js/');

        if ($appFileSearchResult === null || $appFileSearchResult !== $newAppFileName) {
            $coreFilePath = Kernel::getPackagePath([self::CORE_JS, $file]);
            $appFileFolderPath = Kernel::getAppPublicPath([self::JS_PATH]);

            FileHandler::copyFileToFolder($coreFilePath, $appFileFolderPath, $newAppFileName);

            if ($appFileSearchResult !== null && $appFileSearchResult !== $newAppFileName)
                FileHandler::delete(FileHandler::pathFromArray([$appFileFolderPath, $appFileSearchResult]));
        }

        return $newAppFileName;
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
                Kernel::getErrorHandler()->logError('Error reading version file: ' . self::VERSION_FILENAME);

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
        return self::base_uri() . self::JS_PATH;
    }

    public static function css_folder()
    {
        return self::base_uri() . self::CSS_PATH;
    }

    public static function media_folder()
    {
        return self::base_uri() . self::MEDIA_PATH;
    }

    public static function js_src($file)
    {
        $file = self::process_core_js_src($file);

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

    public static function media_src($file)
    {
        return self::filepath(self::media_folder(), $file);
    }
}