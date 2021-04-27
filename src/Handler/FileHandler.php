<?php


namespace Copper\Handler;


use Copper\FunctionResponse;
use Copper\Kernel;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHandler
{
    const ERROR_FILE_NOT_FOUND = 'File not found';
    const ERROR_FILE_OPEN = 'Unable to open file';
    const ERROR_FOLDER_NOT_FOUND = 'Folder not found';
    const ERROR_PUT_CONTENT = 'Unable to save content to file';
    const ERROR_GET_CONTENT = 'Unable to get content from file';

    private static function extractNamespaceFromFile($file)
    {
        if (is_dir($file))
            return null;

        $src = file_get_contents($file);

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
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);

        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');

        $absolutes = array();

        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return (($withDirSeparator) ? DIRECTORY_SEPARATOR : '') . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function createFolder($folderPath)
    {
        $response = new FunctionResponse();

        $folderPath = self::getAbsolutePath($folderPath);

        $createStatus = mkdir($folderPath);

        return $response->okOrFail($createStatus);
    }

    public static function pathFromArray(array $pathArray)
    {
        return join(DIRECTORY_SEPARATOR, $pathArray);
    }

    public static function packagePathFromArray(array $pathArray)
    {
        return join(DIRECTORY_SEPARATOR, array_merge([Kernel::getPackagePath()], $pathArray));
    }

    public static function projectPathFromArray(array $pathArray)
    {
        return join(DIRECTORY_SEPARATOR, array_merge([Kernel::getProjectPath()], $pathArray));
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

        return $response->result(file_get_contents($filePath), self::ERROR_GET_CONTENT);
    }

    /**
     * @param string $filePath
     * @param int $line_num
     *
     * @return string|null
     */
    public static function readLine(string $filePath, int $line_num)
    {
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
     * @param string $filePath
     * @param string $content
     * @param bool $lock
     * @param bool $create_new
     *
     * @return FunctionResponse
     */
    public static function appendContent(string $filePath, string $content, bool $create_new = false, bool $lock = false)
    {
        $response = new FunctionResponse();

        if (self::fileExists($filePath) === false && $create_new === false)
            return $response->error(self::ERROR_FILE_NOT_FOUND, $filePath);

        $flags = ($lock) ? FILE_APPEND | LOCK_EX : FILE_APPEND;

        return $response->result(file_put_contents($filePath, $content, $flags), self::ERROR_PUT_CONTENT);
    }

    /**
     * Set content of a file
     *
     * @param string $filePath
     * @param string $content
     * @param bool $create_new
     *
     * @return FunctionResponse
     */
    public static function setContent(string $filePath, string $content, bool $create_new = false)
    {
        $response = new FunctionResponse();

        if (self::fileExists($filePath) === false && $create_new === false)
            return $response->error(self::ERROR_FILE_NOT_FOUND, $filePath);

        return $response->result(file_put_contents($filePath, $content), self::ERROR_PUT_CONTENT);
    }

    /**
     * Create new file with content
     *
     * @param string $filePath
     * @param string $content
     *
     * @return FunctionResponse
     */
    public static function create(string $filePath, string $content)
    {
        return self::setContent($filePath, $content, true);
    }

    /**
     * Copy file to folder
     *
     * @param string $filePath
     * @param string $destFolderPath
     *
     * @return FunctionResponse
     */
    public static function copyFileToFolder(string $filePath, string $destFolderPath)
    {
        $response = new FunctionResponse();

        $filePath = self::getAbsolutePath($filePath);
        $destFolderPath = self::getAbsolutePath($destFolderPath);

        if (self::fileExists($filePath) === false)
            return $response->error(self::ERROR_FILE_NOT_FOUND, $filePath);

        if (self::fileExists($destFolderPath) === false)
            return $response->error(self::ERROR_FOLDER_NOT_FOUND, $destFolderPath);

        $filename = basename($filePath);

        $copyStatus = copy($filePath, $destFolderPath . '/' . $filename);

        return $response->okOrFail($copyStatus);
    }

    public static function fileExists($filePath)
    {
        return file_exists($filePath);
    }

    /**
     * @param string $filePath
     *
     * @return FunctionResponse
     */
    public static function delete(string $filePath)
    {
        if (self::fileExists($filePath) === false)
            return FunctionResponse::createError('File does not exist');

        return FunctionResponse::createSuccessOrError(unlink($filePath));
    }

    public static function getFilesInFolder($folderPath)
    {
        $response = new FunctionResponse();

        if (file_exists($folderPath) === false)
            return $response->error(self::ERROR_FOLDER_NOT_FOUND, $folderPath);

        $files = array_diff(scandir($folderPath), array('.', '..'));

        return $response->result($files);
    }

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

    public static function getFileClassName($filePath)
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $filePath);

        $fileName = end($pathParts);
        $className = str_replace('.php', '', $fileName);
        $namespace = self::extractNamespaceFromFile($filePath);

        return $namespace . '\\' . $className;
    }

    public static function getClassNamesInFolder($folderPath)
    {
        $response = self::getFilesInFolder($folderPath);

        if ($response->hasError())
            return $response->error($response->msg);

        $classNames = [];

        foreach ($response->result as $file) {
            $filePath = $folderPath . '/' . $file;

            if (is_dir($filePath))
                continue;

            $classNames[] = self::getFileClassName($filePath);
        }

        return $response->result($classNames);
    }

    public static function readCSV(UploadedFile $file, $fieldNames = [], $colCount = 0, $delimiter = ';')
    {
        $response = new FunctionResponse();

        $fieldNames = array_values($fieldNames);

        $rows = [];

        $handle = fopen($file->getPathname(), "r");
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
            $response->fail(self::ERROR_FILE_OPEN, $file->getPathname());
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
        return rename($oldName, $newName);
    }

    // ------------- Aliases --------------------

    /**
     * Alias for getContent
     *
     * @param string $filePath
     *
     * @return FunctionResponse
     */
    public static function read(string $filePath)
    {
        return self::getContent($filePath);
    }

}