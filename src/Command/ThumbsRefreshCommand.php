<?php

namespace App\Command;

use App\Service\PictureConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'thumbs:refresh',
    description: 'Refresh all thumbnails',
)]
class ThumbsRefreshCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager, private PictureConverter $pictureConverter)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pictureCount = $this->entityManager->createQuery('SELECT COUNT(p) FROM App\Entity\Picture p WHERE p.status = 1')->getSingleScalarResult();

        $io->info(sprintf('Found %d pictures.', $pictureCount));

        $io->progressStart($pictureCount);

        $pictureQuery = $this->entityManager->createQuery('SELECT p FROM App\Entity\Picture p WHERE p.status = 1');

        foreach ($pictureQuery->toIterable() as $picture) {
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
