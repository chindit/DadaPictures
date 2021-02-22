<?php

declare(strict_types=1);

namespace App\Factory;

use App\Service\ArchiveHandler\ArchiveHandlerInterface;
use App\Service\ArchiveHandler\RarReader;
use App\Service\ArchiveHandler\TarGzReader;
use App\Service\ArchiveHandler\ZipReader;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ArchiveFactory
 * @package App\Factory
 */
class ArchiveFactory
{
    public static function getHandler(File $file): ArchiveHandlerInterface
    {
        return match ($file->getMimeType()) {
            'application/zip' => new ZipReader(),
            'application/x-rar' => new RarReader(),
            'application/gzip', 'application/x-tar' => new TarGzReader(),
            default => throw new \InvalidArgumentException("Type «" . $file->getMimeType() . "» is not supported"),
        };
    }
}
