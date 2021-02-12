<?php
declare(strict_types=1);

namespace App\Service;


use App\Entity\Picture;

final class Path
{
    public function __construct(private string $storagePath)
    {
    }

    public function getTempDirectory(): string
    {
        return $this->storagePath . (str_ends_with($this->storagePath, '/') ? '' : '/') . 'pictures/temp/';
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
}