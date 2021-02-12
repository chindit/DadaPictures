<?php

namespace App\Message;

class PackMessage
{
    private string $archiveName;

    public function __construct(string $archiveName)
    {
        $this->archiveName = $archiveName;
    }

    public function getArchive(): string
    {
        return $this->archiveName;
    }
}
