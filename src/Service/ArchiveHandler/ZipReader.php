<?php

declare(strict_types=1);

namespace App\Service\ArchiveHandler;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\File;
use ZipArchive;

/**
 * Class ZipReader
 * @package App\Service\ArchiveHandler
 */
class ZipReader implements ArchiveHandlerInterface
{
    /**
     * Return the list of files content in the archive
     * @param File $file
     * @return array<int, string>
     */
    public function getContent(File $file): array
    {
        $zip = new ZipArchive();
        $return = [];
        if ($zip->open($file->getPathname()) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if ($name === false) {
                    continue;
                }
                $return[] = $name;
            }
            $zip->close();

            return $return;
        }

        throw new IOException('Unable to open file «' . $file->getFilename() . '»');
    }

    /**
     * Extract archive in given path
     * @param File $file
     * @param string $extractPath
     * @return bool
     */
    public function extractArchive(File $file, string $extractPath): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($file->getPathname()) === true) {
            $result = $zip->extractTo($extractPath);
            $zip->close();

            return $result;
        }

        throw new IOException('Unable to open file «' . $file->getFilename() . '»');
    }
}
