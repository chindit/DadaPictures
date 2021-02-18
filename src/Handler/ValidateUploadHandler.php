<?php
declare(strict_types=1);

namespace App\Handler;


use App\Entity\Pack;
use App\Message\UploadMessage;

class ValidateUploadHandler extends UploadHandler
{
    public function __invoke(UploadMessage $upload): void
    {
        /** @var Pack $pack */
        $pack = $this->packRepository->find($upload->getContents());

        $this->uploadManager->validateUpload($pack);
    }
}