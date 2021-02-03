<?php


namespace Copper;


use App\Entity\WarehouseEntity;
use App\Resource\Warehouse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
            $response->fail('error opening the file.');
        }

        return $response->ok('success', $rows);
    }
}