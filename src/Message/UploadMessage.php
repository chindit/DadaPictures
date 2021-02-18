<?php
declare(strict_types=1);

namespace App\Messaget;


class UploadMessage
{
    public function __construct(private int $packId)
    {
    }

    public function getContents(): string
    {
        return (string)$this->packId;
    }
}