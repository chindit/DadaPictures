<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Pack;
use App\Message\ValidateUploadMessage;
use App\Repository\PackRepository;
use App\Service\UploadManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ValidateUploadHandler implements MessageHandlerInterface
{
    public function __construct(
        protected PackRepository $packRepository,
        protected UploadManager $uploadManager
    ) {
    }

    public function __invoke(ValidateUploadMessage $upload): void
    {
        /** @var Pack $pack */
        $pack = $this->packRepository->find($upload->getContents());

        $this->uploadManager->validateUpload($pack);
    }
}
