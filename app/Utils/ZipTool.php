<?php

namespace App\Utils;

class ZipTool
{
    public static function compressFileFolder($zipPath = '', $folderPath = '')
    {
        $rootPath = realpath($folderPath);

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $fileContent = file_get_contents($filePath);
                $zip->addFromString($relativePath, $fileContent);
            }
        }

        $zip->close();
    }
}
