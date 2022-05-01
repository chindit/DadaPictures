<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\TranslatedTag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1) Create user
        $user = (new User())
            ->setUsername('chindit')
            ->setEmail('littletiger58@gmail.com')
            ->setPassword('$2y$13$5p/BqeV40TliYBPsyB5Au.r0rN8T1LDziHIlzJN.kpQLfEcrou7ae')
            ->setLanguage('fr')
            ->setRoles(['ROLE_USER', 'ROLE_EDITOR', 'ROLE_ADMIN']);
        $manager->persist($user);

        // 2) Create tags
        $tags = [
            [
                'en' => 'Tag 1 EN',
                'fr' => 'Tag 1 FR'
            ],
            [
                'en' => 'Tag 2 EN',
                'fr' => 'Tag 2 FR'
            ],[
                'en' => 'Tag 3 EN',
                'fr' => 'Tag 3 FR'
            ],
        ];

        foreach ($tags as $tag) {
            $tagEntity = (new Tag())
                ->setName((new AsciiSlugger())->slug($tag['en']))
                ->addTranslation((new TranslatedTag())->setLanguage('en')->setName($tag['en']))
                ->addTranslation((new TranslatedTag())->setLanguage('fr')->setName($tag['fr']));

            $manager->persist($tagEntity);
        }

        $manager->flush();
    }
}
