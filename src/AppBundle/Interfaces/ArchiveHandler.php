<?php
declare(strict_types=1);

namespace AppBundle\Interfaces;


use Symfony\Component\HttpFoundation\File\File;

interface ArchiveHandler
{
    public function extractArchive(File $file, string $extractPath) : bool;
}