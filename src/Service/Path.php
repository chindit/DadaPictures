<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Picture;

final class Path
{
    public function __construct(private string $storagePath, string $rootDir)
    {
        if (!str_starts_with($this->storagePath, $rootDir)) {
            $this->storagePath = $rootDir . $this->storagePath;
        }
    }

    public function getTempDirectory(): string
    {
        return $this->storagePath . (str_ends_with($this->storagePath, '/') ? '' : '/') . 'pictures/temp/';
    }

    /**
     * @deprecated
     */
    public function getTempUploadDirectory(): string
    {
        return $this->storagePath . (str_ends_with($this->storagePath, '/') ? '' : '/') . 'tmp/';
    }

    public function getStorageDirectory(): string
    {
        return $this->storagePath . (str_ends_with($this->storagePath, '/') ? '' : '/') . 'pictures/';
    }

    public function getPictureFullpath(Picture $picture): string
    {
        if (file_exists($this->getTempDirectory() . $picture->getFilename())) {
            return $this->getTempDirectory() . $picture->getFilename();
        } else {
            return $this->getStorageDirectory() . $picture->getFilename();
        }
    }

    public function getThumbnailsDirectory(): string
    {
        return $this->getStorageDirectory() . 'thumbs/';
    }
}
