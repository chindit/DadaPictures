<?php

namespace App\Command;

use App\Service\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class SetupCommand extends Command
{
    protected static $defaultName = 'app:setup';
    protected static $defaultDescription = 'Setup directory structure';

    public function __construct(string $name = null, private Path $path)
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
        $filesystem = new Filesystem();

        $io->writeln('Checking directories…');

        $io->writeln('Checking storage directory');
        if (!$filesystem->exists($this->path->getStorageDirectory())) {
        	$io->warning('Storage directory does not exist.  Creating');
        	$filesystem->mkdir($this->path->getStorageDirectory());
        }

        $io->writeln('Checking temp directory');
        if (!$filesystem->exists($this->path->getTempDirectory())) {
        	$io->warning('Temp directory does not exist.  Creating');
        	$filesystem->mkdir($this->path->getTempDirectory());
        }

        $io->writeln('Checking temp upload directory');
        if (!$filesystem->exists($this->path->getTempUploadDirectory())) {
        	$io->warning('Temp upload directory does not exist.  Creating');
        	$filesystem->mkdir($this->path->getTempUploadDirectory());
        }

        $io->writeln('Checking thumbnail directory');
        if (!$filesystem->exists($this->path->getThumbnailsDirectory())) {
        	$io->warning('Thumbnail directory does not exist.  Creating');
        	$filesystem->mkdir($this->path->getThumbnailsDirectory());
        }

        $io->success('All directories are OK');

        return Command::SUCCESS;
    }
}
