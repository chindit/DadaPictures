<?php

namespace App\Command;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use App\Service\PictureConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PictureThumbsCommand extends Command
{
    protected static $defaultName = 'picture:thumbs';
    protected static string $defaultDescription = 'Generate missing thumbnails';

    public function __construct(
        string $name = null,
        private PictureRepository $pictureRepository,
        private PictureConverter $pictureConverter,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
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
