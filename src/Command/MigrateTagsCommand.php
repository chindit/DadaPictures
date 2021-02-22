<?php

namespace App\Command;

use App\Entity\Tag;
use App\Entity\TranslatedTag;
use App\Model\Languages;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateTagsCommand extends Command
{
    protected static $defaultName = 'migrate:tags';
    protected static $defaultDescription = 'Migrate tags to translated tags';

    public function __construct(string $name = null, private EntityManagerInterface $entityManager, private TagRepository $tagRepository)
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

        /** @var Tag[] $tags */
        $tags = $this->tagRepository->findAll();

        foreach ($tags as $tag) {
            $translatedTag = new TranslatedTag();
            $translatedTag->setName($tag->getName());
            $translatedTag->setLanguage(Languages::FR);
            $tag->setName(uniqid(more_entropy: true));
            $tag->addTranslation($translatedTag);
        }

        $this->entityManager->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
