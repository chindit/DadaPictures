<?php
declare(strict_types=1);

namespace AppBundle\Service\ArchiveHandler;


use Symfony\Component\HttpFoundation\File\File;

/**
 * Interface ArchiveHandler
 * Interface for all archive handers
 *
 * @package AppBundle\Service\ArchiveHandler
 */
interface ArchiveHandlerInterface
{
    public function extractArchive(File $file, string $extractPath) : bool;
}
