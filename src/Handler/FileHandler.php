<?php


namespace Copper\Handler;


use Copper\FunctionResponse;
use Copper\Kernel;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileHandler
 * @package Copper\Handler
 */
class FileHandler
{
    const DIR_SEPARATOR = '/';

    const ERROR_FILE_NOT_FOUND = 'File not found';
    const ERROR_FILE_OPEN = 'Unable to open file';
    const ERROR_FOLDER_NOT_FOUND = 'Folder not found';
    const ERROR_PUT_CONTENT = 'Unable to save content to file';
    const ERROR_GET_CONTENT = 'Unable to get content from file';

    const MIME_TYPE__IMAGE_JPEG = 'image/jpeg';
    const MIME_TYPE__IMAGE_PNG = 'image/png';

    /**
     * Get directory name from path
     * @param $path
     * @return mixed|string
     */
    public static function getDirname($path)
    {
        $path_parts = pathinfo($path);

        return $path_parts['dirname'] ?? null;
    }

    /**
     * @param $filepath
     * @return int
     */
    public static function getFilesize($filepath)
    {
        if (self::fileExists($filepath) === false)
            return 0;

        $filesize = filesize($filepath);

        return ($filesize === false) ? 0 : $filesize;
    }

    /**
     * Set/change extension for file
     * @param $filepath
     * @param $newExtension
     * @return string
     */
    public static function setExtension($filepath, $newExtension)
    {
        $extension = FileHandler::getExtension($filepath);

        if ($extension === null)
            $filepath .= '.' . $newExtension;
        else
            $filepath = StringHandler::replace($filepath, $extension, $newExtension);

        return $filepath;
    }

    /**
     * Get file extension from filepath
     * @param $filepath
     * @return null|string
     */
    public static function getExtension($filepath)
    {
        $path_parts = pathinfo($filepath);

        return $path_parts['extension'] ?? null;
    }

    /**
     * Get filename from filepath
     * @param $filepath
     * @return string
     */
    public static function getFilename($filepath)
    {
        $parsedUrl = parse_url($filepath, PHP_URL_PATH);

        return basename($parsedUrl);
    }

    /**
     * Clean File Path (allowed characters: 0-9_-./A-Za-z)
     * <hr>
     * <code>
     * - cleanPath('phar://exec<x>\'"ute/../../ute.jpg') // returns phar/execxute/ute.jpg
     * </code>
     * @param string|null|bool $path Path to file
     * @param bool $relativeSupport Relative support - path "../../" is supported, else "../" is replaced to "/"
     *
     * @return string
     */
    public static function cleanPath($path, $relativeSupport = false)
    {
        if ($path === null || is_bool($path))
            return '';

        // Windows Hack
        $path = str_replace(['/', '\\'], self::DIR_SEPARATOR, $path);

        // strip bad chars
        $res = StringHandler::regexReplace($path, '/[^0-9_\-.\/A-Za-z:]/m', '');

        // absolute path only
        if ($relativeSupport === false)
            $res = StringHandler::replace($res, ['../'], ['/']);

        $res = StringHandler::replaceRecursively($res, '//', '/');

        // Windows fix for path starting with /C:/home
        if (strlen($res) > 3 && $res[0] === '/' && $res[2] === ':')
            $res = ltrim($res, '/');

        return $res;
    }

    /**
     * @param $file
     * @return mixed|null
     */
    private static function extractNamespaceFromFile($file)
    {
        if (is_dir(self::cleanPath($file, true)))
            return null;

        $res = self::getContent($file);

        if ($res->hasError())
            return null;

        $src = $res->result;

        if (preg_match('#^namespace\s+(.+?);$#sm', $src, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Transforms path to absolute path (without .././)
     *
     * @param $path
     * @param bool $withDirSeparator
     *
     * @return string
     */
    public static function getAbsolutePath($path, $withDirSeparator = true)
    {
        $path = str_replace(array('/', '\\'), self::DIR_SEPARATOR, $path);

        $parts = array_filter(explode(self::DIR_SEPARATOR, $path), 'strlen');

        $absolutes = array();

        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return (($withDirSeparator) ? self::DIR_SEPARATOR : '') . implode(self::DIR_SEPARATOR, $absolutes);
    }

    /**
     * Create new folder
     *
     * @param $folderPath
     * @param bool $skipIfExists
     * @return FunctionResponse
     */
    public static function createFolder($folderPath, $skipIfExists = true)
    {
        $response = new FunctionResponse();

        $folderPath = self::getAbsolutePath($folderPath);

        $folderPath = self::cleanPath($folderPath, true);

        if (self::fileExists($folderPath))
            return ($skipIfExists)
                ? $response->ok('Folder creation skipped')
                : $response->fail('Folder already exists');

        $createStatus = mkdir($folderPath);

        return $response->okOrFail($createStatus);
    }

    /**
     * Path from array
     *
     * @param array $pathArray
     * @return string
     */
    public static function pathFromArray(array $pathArray)
    {
        return join('/', $pathArray);
    }

    /**
     * Extend path array with another path as string/array
     *
     * @param array $pathArray
     * @param string|array|null $path
     * @return array
     */
    public static function extendPathArray(array $pathArray, $path = null)
    {
        if ($path !== null) {
            if (VarHandler::isArray($path) === false)
                $pathArray[] = $path;
            else
                $pathArray = ArrayHandler::merge($pathArray, $path);
        }

        foreach ($pathArray as $key => $value) {
            if ($key !== 0)
                $value = ltrim($value, '/');

            $value = rtrim($value, '/');

            $pathArray[$key] = $value;
        }

        return $pathArray;
    }

    /**
     * Package path from array
     *
     * @param array $pathArray
     * @return string
     */
    public static function packagePathFromArray(array $pathArray)
    {
        return join(self::DIR_SEPARATOR, array_merge([Kernel::getPackagePath()], $pathArray));
    }

    /**
     * App path from array
     *
     * @param array $pathArray
     * @return string
     */
    public static function appPathFromArray(array $pathArray)
    {
        return join(self::DIR_SEPARATOR, array_merge([Kernel::getAppPath()], $pathArray));
    }

    /**
     * @param string $filePath
     *
     * @return FunctionResponse
     */
    public static function getContent(string $filePath)
    {
        $response = new FunctionResponse();

        if (self::fileExists($filePath) === false)
            return $response->error(self::ERROR_FILE_NOT_FOUND, $filePath);

        $filePath = self::cleanPath($filePath, true);

        return $response->result(file_get_contents($filePath), self::ERROR_GET_CONTENT);
    }

    /**
     * @param string $filePath
     * @param int $line_num
     *
     * @return string|null
     */
    public static function readLine(string $filePath, int $line_num): ?string
    {
        $filePath = self::cleanPath($filePath, true);

        if (FileHandler::fileExists($filePath) === false)
            return null;

        // TODO handle exceptions
        $file = new SplFileObject($filePath);

        $content = null;

        if (!$file->eof()) {
            $file->seek($line_num);
            $content = $file->current();
        }

        return $content;
    }

    /**
     * Append content to the end of a file
     *
     * @param string $filePath File path
     * @param string $content File content
     * @param bool $onlyIfExists [optional] = false
     * <p>If is true and filepath doesn't exists - response with error is returned</p>
     *
     * @return FunctionResponse
     */
    public static function appendContent(string $filePath, string $content, bool $onlyIfExists = false)
    {
        $response = new FunctionResponse();

        if (self::fileExists($filePath) === false && $onlyIfExists === true)
            return $response->error(self::ERROR_FILE_NOT_FOUND, $filePath);

        $filePath = self::cleanPath($filePath, true);

        return $response->result(file_put_contents($filePath, $content, FILE_APPEND), self::ERROR_PUT_CONTENT);
    }

    /**
     * Set content of a file
     *
     * @param string $filePath File path
     * @param string $content File content
     * @param bool $onlyIfExists [optional] = false
     * <p>If is true and filepath doesn't exists - response with error is returned</p>
     * @param bool $lock [optional] = false
     * <p>If is true the file will be locked while content is being saved to file</p>
     *
     * @return FunctionResponse
     */
    public static function setContent(string $filePath, string $content, bool $onlyIfExists = false, $lock = false)
    {
        $response = new FunctionResponse();

        if (self::fileExists($filePath) === false && $onlyIfExists === true)
            return $response->error(self::ERROR_FILE_NOT_FOUND, $filePath);

        $filePath = self::cleanPath($filePath, true);

        $flags = ($lock) ? LOCK_EX : 0;

        return $response->result(file_put_contents($filePath, $content, $flags), self::ERROR_PUT_CONTENT);
    }

    /**
     * Copy file to folder
     *
     * @param string $filePath
     * @param string $destFolderPath
     * @param string $newFileName
     *
     * @return FunctionResponse
     */
    public static function copyFileToFolder(string $filePath, string $destFolderPath, string $newFileName = null)
    {
        $response = new FunctionResponse();

        $filePath = self::getAbsolutePath($filePath);
        $destFolderPath = self::getAbsolutePath($destFolderPath);

        if (self::fileExists($filePath) === false)
            return $response->error(self::ERROR_FILE_NOT_FOUND, $filePath);

        if (self::fileExists($destFolderPath) === false)
            return $response->error(self::ERROR_FOLDER_NOT_FOUND, $destFolderPath);

        $filePath = self::cleanPath($filePath, true);
        $destFolderPath = self::cleanPath($destFolderPath, true);

        $filename = ($newFileName === null) ? basename($filePath) : $newFileName;

        $copyStatus = copy($filePath, $destFolderPath . '/' . $filename);

        return $response->okOrFail($copyStatus);
    }

    /**
     * Check filepath
     *
     * @param $filepath
     * @return bool
     */
    public static function checkFilepath($filepath)
    {
        // transfer windows path to unix path before compare
        $filepath = StringHandler::replace($filepath, '\\', '/');

        $cleanedFilepath = FileHandler::cleanPath($filepath);

        return $filepath === $cleanedFilepath;
    }

    /**
     * Check if file exists
     *
     * @param $filePath
     * @return bool
     */
    public static function fileExists($filePath)
    {
        $filePath = self::cleanPath($filePath, true);

        return file_exists($filePath);
    }

    /**
     * @param string|array $filePath
     *
     * @return FunctionResponse
     */
    public static function delete($filePath)
    {
        if (VarHandler::isArray($filePath))
            $filePath = self::pathFromArray($filePath);

        if (self::fileExists($filePath) === false)
            return FunctionResponse::createError('File does not exist');

        $filePath = self::cleanPath($filePath, true);

        return FunctionResponse::createSuccessOrError(unlink($filePath));
    }

    /**
     * Gets file modification time
     *
     * @param $filePath
     * @return false|int
     */
    public static function getModTime($filePath)
    {
        return FileHandler::fileExists($filePath) ? filemtime($filePath) : false;
    }

    /**
     * @param $folderPath
     * @param false $withModTimeAsKey
     * @return FunctionResponse
     */
    public static function getFilesInFolder($folderPath, $withModTimeAsKey = false)
    {
        $response = new FunctionResponse();

        if (self::fileExists($folderPath) === false)
            return $response->error(self::ERROR_FOLDER_NOT_FOUND, $folderPath);

        $folderPath = self::cleanPath($folderPath, true);

        $files = array_diff(scandir($folderPath), array('.', '..'));

        if ($withModTimeAsKey) {

            $files_with_mod_time = [];

            foreach ($files as $k => $file) {
                $files_with_mod_time[FileHandler::getModTime($folderPath . '/' . $file) . '_' . $k] = $file;
            }

            $files = $files_with_mod_time;
        }

        return $response->result($files);
    }

    /**
     * @param $filePath
     * @return array
     */
    public static function getFileConstantList($filePath)
    {
        $contentResponse = FileHandler::getContent($filePath);

        if ($contentResponse->hasError())
            return [];

        $content = $contentResponse->result;

        $groups = StringHandler::regexAll($content, '/const (.*?) = (.*?);/ms');

        $fields = [];

        foreach ($groups as $key => $match) {
            $evalStr = 'return ' . $match[2] . ';';
            $fields[$match[1]] = eval($evalStr);
        }

        return $fields;
    }

    /**
     * @param $filePath
     * @return string
     */
    public static function getFileClassName($filePath)
    {
        $pathParts = explode(self::DIR_SEPARATOR, $filePath);

        $fileName = end($pathParts);
        $className = str_replace('.php', '', $fileName);
        $namespace = self::extractNamespaceFromFile($filePath);

        return $namespace . '\\' . $className;
    }

    /**
     * @param $folderPath
     * @return FunctionResponse
     */
    public static function getClassNamesInFolder($folderPath)
    {
        $response = self::getFilesInFolder($folderPath);

        if ($response->hasError())
            return $response->error($response->msg, []);

        $classNames = [];

        foreach ($response->result as $file) {
            $filePath = $folderPath . '/' . $file;

            $filePath = self::cleanPath($filePath, true);

            if (is_dir($filePath))
                continue;

            $classNames[] = self::getFileClassName($filePath);
        }

        return $response->result($classNames);
    }

    /**
     * @param $filePath
     * @param array $fieldNames
     * @return FunctionResponse
     */
    public static function readTSV($filePath, $fieldNames = [])
    {
        $response = new FunctionResponse();

        $fieldNames = array_values($fieldNames);

        $rows = [];

        if (self::fileExists($filePath) === false)
            return $response->error(self::ERROR_FILE_NOT_FOUND, $filePath);

        $handle = fopen($filePath, "r");
        if ($handle) {

            while (($line = fgets($handle)) !== false) {
                $row = explode("\t", $line);

                // Remove Line Breaks
                foreach ($row as $k => $v) {
                    $row[$k] = str_replace(PHP_EOL, '', $v);
                }

                // register field names
                if (count($fieldNames) === count($row)) {
                    $entry = [];
                    foreach ($fieldNames as $key => $value) {
                        $entry[$value] = $row[$key];
                    }
                    $rows[] = $entry;
                } else if (count($fieldNames) === 0) {
                    $rows[] = $row;
                }
            }

            fclose($handle);
        } else {
            $response->fail(self::ERROR_FILE_OPEN, $filePath);
        }

        return $response->result($rows);
    }

    /**
     * Read CSV
     *
     * @param UploadedFile $file
     * @param array $fieldNames
     * @param int $colCount
     * @param string $delimiter
     *
     * @return FunctionResponse
     */
    public static function readCSV(UploadedFile $file, $fieldNames = [], $colCount = 0, $delimiter = ';')
    {
        $response = new FunctionResponse();

        $fieldNames = array_values($fieldNames);

        $rows = [];

        $filePath = $file->getPathname();

        if (self::fileExists($filePath) === false)
            return $response->error(self::ERROR_FILE_NOT_FOUND, $filePath);

        $filePath = self::cleanPath($filePath, true);

        $handle = fopen($filePath, "r");
        if ($handle) {
            $rowForFix = [];

            while (($line = fgets($handle)) !== false) {
                $row = explode($delimiter, $line);

                // Fix Broken Rows (broken by Line Breaks)
                if (count($row) !== $colCount && ((count($rowForFix) + count($row) - 1) <= $colCount)) {
                    $lastEntry = array_pop($rowForFix);

                    if ($lastEntry !== NULL)
                        $row[0] = str_replace(PHP_EOL, '', $lastEntry . $row[0]);

                    $rowForFix = array_merge($rowForFix, $row);

                    if (count($rowForFix) === $colCount) {
                        $row = $rowForFix;
                        $rowForFix = [];
                    } else {
                        continue;
                    }
                }

                // Remove Line Breaks
                foreach ($row as $k => $v) {
                    $row[$k] = str_replace(PHP_EOL, '', $v);
                }

                // register field names
                if (count($fieldNames) === count($row)) {
                    $entry = [];
                    foreach ($fieldNames as $key => $value) {
                        $entry[$value] = $row[$key];
                    }
                    $rows[] = $entry;
                } else if (count($fieldNames) === 0) {
                    $rows[] = $row;
                }
            }

            fclose($handle);
        } else {
            $response->fail(self::ERROR_FILE_OPEN, $filePath);
        }

        return $response->result($rows);
    }

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @return bool
     */
    public static function rename(string $oldName, string $newName)
    {
        $oldName = self::cleanPath($oldName, true);

        if (self::fileExists($oldName) === false)
            return false;

        $newName = self::cleanPath($newName, true);

        return rename($oldName, $newName);
    }

    // ----------------------------- Aliases -------------------------

    /**
     * @param $path
     * @return mixed|string|null
     * @see getDirname()
     */
    public static function getFolderName($path)
    {
        return self::getDirname($path);
    }

    /**
     * @param string $filePath File path
     * @param string $content File content
     * @param bool $onlyIfExists [optional] = false
     * @return FunctionResponse
     * @see appendContent()
     *
     */
    public static function addContent(string $filePath, string $content, bool $onlyIfExists = false): FunctionResponse
    {
        return self::appendContent($filePath, $content, $onlyIfExists);
    }

}
