<?php
declare(strict_types=1);

namespace AppBundle\Factory;

use Symfony\Component\HttpFoundation\File\File;

class ArchiveFactory
{
    public static function getHandler(File $file)
    {
        switch ($file->getMimeType()) {
            case 'application/zip':
                return new \AppBundle\Service\ArchiveHandler\ZipReader();
                break;
            default:
                throw new \InvalidArgumentException("Type «" . $file->getMimeType() . "» is not supported");
        }
    }
}