<?php
declare(strict_types=1);

namespace AppBundle\Factory;

use AppBundle\Service\ArchiveHandler\RarReader;
use Symfony\Component\HttpFoundation\File\File;

class ArchiveFactory
{
    public static function getHandler(File $file)
    {
        switch ($file->getMimeType()) {
            case 'application/zip':
                return new \AppBundle\Service\ArchiveHandler\ZipReader();
                break;
            case 'application/rar':
                return new RarReader();
            default:
                throw new \InvalidArgumentException("Type «" . $file->getMimeType() . "» is not supported");
        }
    }
}
