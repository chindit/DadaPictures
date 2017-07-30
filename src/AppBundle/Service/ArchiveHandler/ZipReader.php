<?php
declare(strict_types=1);

namespace AppBundle\Service\ArchiveHandler;

use AppBundle\Interfaces\ArchiveHandler;
use Symfony\Component\HttpFoundation\File\File;
use ZipArchive;

class ZipReader implements ArchiveHandler
{
    /** @var File */
    private $file;

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

    public function extractArchive(File $file) : string
    {
        $zip = new ZipArchive;
        if ($zip->open($file->getPathname()) === true) {
            $path = '/srv/http/DadaPictures/web/pictures/temp/';
            $dirName = uniqid('temp_');
            mkdir($path . $dirName);
            $zip->extractTo($path . $dirName);
            $zip->close();
            return $path . $dirName;
        }
        return '';
    }
}