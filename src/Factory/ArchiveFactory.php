<?php
declare(strict_types=1);

namespace App\Factory;

use App\Service\ArchiveHandler\RarReader;
use App\Service\ArchiveHandler\ZipReader;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ArchiveFactory
 * @package App\Factory
 */
class ArchiveFactory
{
    public static function getHandler(File $file)
    {
        switch ($file->getMimeType()) {
            case 'application/zip':
                return new ZipReader();
                break;
            case 'application/x-rar':
                return new RarReader();
            default:
                throw new \InvalidArgumentException("Type «" . $file->getMimeType() . "» is not supported");
        }
    }
}
