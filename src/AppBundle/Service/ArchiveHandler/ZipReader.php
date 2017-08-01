<?php
declare(strict_types=1);

namespace AppBundle\Service\ArchiveHandler;

use Symfony\Component\HttpFoundation\File\File;
use ZipArchive;

/**
 * Class ZipReader
 * @package AppBundle\Service\ArchiveHandler
 */
class ZipReader implements ArchiveHandler
{
    /** @var File */
    private $file;

    /**
     * Return the list of files content in the archive
     * @param File $file
     * @return array
     */
    public function getContent(File $file) : array
    {
        $this->file = $file;
        $zip = new ZipArchive;
        $return = [];
        if ($zip->open($file->getPathname()) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $return[] = $zip->getNameIndex($i);
            }
            $zip->close();
            return $return;
        }
        return [];
    }

    /**
     * Extract archive in given path
     * @param File $file
     * @param string $extractPath
     * @return bool
     */
    public function extractArchive(File $file, string $extractPath) : bool
    {
        $zip = new ZipArchive;
        $result = false;
        if ($zip->open($file->getPathname()) === true) {
            $result = $zip->extractTo($extractPath);
            $zip->close();
        }
        return $result;
    }
}