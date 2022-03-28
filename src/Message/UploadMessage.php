<?php

declare(strict_types=1);

namespace App\Message;

class UploadMessage
{
    public function __construct(private string $packId)
    {
    }

    public function getContents(): string
    {
        return $this->packId;
    }
}
