<?php
declare(strict_types=1);

namespace AppBundle\Service\ArchiveHandler;


use RarArchive;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class RarReader
 * @package AppBundle\Service\ArchiveHandler
 */
class RarReader implements ArchiveHandlerInterface
{
    /**
     * Return the list of files content in the archive
     * @param File $file
     * @return array
     */
    public function getContent(File $file) : array
    {
        $rar = RarArchive::open($file->getPathname());
        if ($rar === false) {
            return [];
        }
        $files = $rar->getEntries();

        $fileList = [];

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
    public function extractArchive(File $file, string $extractPath) : bool
    {
        $rar = RarArchive::open($file->getPathname());
        if ($rar === false) {
            return false;
        }

        $entries = $rar->getEntries();

        foreach ($entries as $entry) {
            $entry->extract($extractPath);
        }

        $rar->close();

        return true;
    }
}
