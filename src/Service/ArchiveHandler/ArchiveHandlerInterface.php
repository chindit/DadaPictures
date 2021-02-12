<?php

declare(strict_types=1);

namespace App\Service\ArchiveHandler;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Interface ArchiveHandler
 * Interface for all archive handers
 *
 * @package App\Service\ArchiveHandler
 */
interface ArchiveHandlerInterface
{
    public function extractArchive(File $file, string $extractPath): bool;
}
