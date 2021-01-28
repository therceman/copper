<?php


namespace Copper;


class FileReader
{
    private static function extractNamespaceFromFile($file)
    {
        $src = file_get_contents($file);

        if (preg_match('#^namespace\s+(.+?);$#sm', $src, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function getFilesInFolder($folderPath)
    {
        $response = new FunctionResponse();

        if (file_exists($folderPath) === false)
            return $response->error("[$folderPath] Folder not found");

        $files = array_diff(scandir($folderPath), array('.', '..'));

        return $response->success("ok", $files);
    }

    public static function getFilePathClassName($filePath)
    {
        $pathParts = explode('/', $filePath);

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
            $classNames[] = self::getFilePathClassName($folderPath . '/' . $file);
        }

        return $response->success("ok", $classNames);
    }
}