<?php

namespace App\Command;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use App\Service\PictureConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'picture:thumbs',
    description: 'Generate missing thumbnails',
)]
class PictureThumbsCommand extends Command
{
    public function __construct(
        private PictureRepository $pictureRepository,
        private PictureConverter $pictureConverter,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pictures = $this->pictureRepository->findBy(['thumbnail' => null, 'status' => Picture::STATUS_OK]);

        $io->note(sprintf('%d pictures need a thumbnail', count($pictures)));

        $io->progressStart(count($pictures));
        foreach ($pictures as $picture) {
            $this->pictureConverter->createThumbnail($picture);
            $io->progressAdvance();
        }

        $io->progressFinish();
        $this->entityManager->flush();

        $io->success('All thumbnails have been generated');

        return Command::SUCCESS;
    }
}
