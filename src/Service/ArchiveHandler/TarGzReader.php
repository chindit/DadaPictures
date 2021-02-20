<?php

namespace App\Service\ArchiveHandler;

use Symfony\Component\HttpFoundation\File\File;

class TarGzReader implements ArchiveHandlerInterface
{
    public function extractArchive(File $file, string $extractPath): bool
    {
        $archive = new \PharData($file->getPathname());

        return $archive->extractTo($extractPath, overwrite: true);
    }
}
