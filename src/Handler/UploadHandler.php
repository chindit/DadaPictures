<?php
declare(strict_types=1);

namespace App\Handler;


use App\Entity\Pack;
use App\Message\UploadMessage;
use App\Repository\PackRepository;
use App\Service\UploadManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UploadHandler implements MessageHandlerInterface
{
    public function __construct(
        protected PackRepository $packRepository,
        protected UploadManager $uploadManager
    )
    {
    }

    public function __invoke(UploadMessage $upload): void
    {
        /** @var Pack $pack */
        $pack = $this->packRepository->find($upload->getContents());

        $this->uploadManager->upload($pack);
    }
}
