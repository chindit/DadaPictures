<?php

namespace App\Command;

use App\Service\PictureConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ThumbsRefreshCommand extends Command
{
    protected static $defaultName = 'thumbs:refresh';
    protected static $defaultDescription = 'Refresh all thumbnails';

    public function __construct(?string $name = null, private EntityManagerInterface $entityManager, private PictureConverter $pictureConverter)
    {
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

        $pictureCount = $this->entityManager->createQuery('SELECT COUNT(p) FROM App\Entity\Picture p WHERE p.status = 1')->getSingleScalarResult();

        $io->info(sprintf('Found %d pictures.', $pictureCount));

        $io->progressStart($pictureCount);

        $picureQuery = $this->entityManager->createQuery('SELECT p FROM App\Entity\Picture p WHERE p.status = 1');

        foreach ($picureQuery->toIterable() as $picture) {
            try {
                $this->pictureConverter->createThumbnail($picture, true);
            } catch (\Exception $e) {
                $io->error(sprintf('Unable to create thumb for picture %s.  Error returned is %s', $picture->getFilename(), $e->getMessage()));
            } finally {
                $io->progressAdvance();
            }
        }

        $io->progressFinish();

        $io->success('All thumbs were regenerated');

        return Command::SUCCESS;
    }
}
