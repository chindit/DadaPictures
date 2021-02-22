<?php

namespace App\Command;

use App\Service\PictureConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ThumbsRefreshCommand extends Command
{
    protected static $defaultName = 'thumbs:refresh';
    protected static $defaultDescription = 'Refresh all thumbnails';

    public function __construct(string $name = null, private EntityManagerInterface $entityManager, private PictureConverter $pictureConverter)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pictureCount = $this->entityManager->createQuery('SELECT COUNT(p) FROM App\Entity\Picture p WHERE p.status = 1')->getSingleScalarResult();

        $io->info(sprintf('Found %d pictures.', $pictureCount));

        $io->progressStart($pictureCount);

        $picureQuery = $this->entityManager->createQuery('SELECT p FROM App\Entity\Picture p');

        foreach ($picureQuery->toIterable() as $picture) {
            $this->pictureConverter->createThumbnail($picture, true);
            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success('All thumbs were regenerated');

        return Command::SUCCESS;
    }
}
