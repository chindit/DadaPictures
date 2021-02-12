<?php

declare(strict_types=1);

namespace App\Service\ArchiveHandler;

use RarArchive;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class RarReader
 * @package App\Service\ArchiveHandler
 */
class RarReader implements ArchiveHandlerInterface
{
    /**
     * Return the list of files content in the archive
     * @param File $file
     * @return array<int, string>
     */
    public function getContent(File $file): array
    {
        $rar = RarArchive::open($file->getPathname());
        if ($rar === false) {
            return [];
        }
        $files = $rar->getEntries();

        $fileList = [];

        if ($files === false) {
            return [];
        }

        foreach ($files as $entry) {
            $fileList[] = $entry->getName();
        }

        $rar->close();

        return $fileList;
    }

    /**
     * Extract archive in given path
     * @param File $file
     * @param string $extractPath
     * @return bool
     */
    public function extractArchive(File $file, string $extractPath): bool
    {
        $rar = RarArchive::open($file->getPathname());
        if ($rar === false) {
            return false;
        }

        $entries = $rar->getEntries();

        if ($entries === false) {
            return false;
        }

        foreach ($entries as $entry) {
            $entry->extract($extractPath);
        }

        $rar->close();

        return true;
    }
}
