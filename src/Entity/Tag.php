<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity()
 */
class Tag
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id;

    /**
     * @ORM\Column(name="name", type="string", length=150, unique=true)
     */
    private string $name = '';

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\TranslatedTag", cascade={"persist", "remove"})
     *
     * @var Collection<int, TranslatedTag> $translations
     */
    private Collection $translations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Picture", mappedBy="tags")
     *
     * @var Collection<int, Picture> $pictures
     */
    private Collection $pictures;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, TranslatedTag>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(TranslatedTag $tag): self
    {
        $this->translations[] = $tag;

        return $this;
    }

    public function getTranslation(string $language, bool $strict = false): ?TranslatedTag
    {
        /** @var TranslatedTag $translation */
        foreach ($this->translations as $translation) {
            if ($translation->getLanguage() === $language) {
                return $translation;
            }
        }

        if ($strict) {
            return null;
        }

        foreach ($this->translations as $translation) {
            if ($translation->getLanguage() === 'en') {
                return $translation;
            }
        }

        if (count($this->translations) === 0) {
            return null;
        }

        return $this->translations[0];
    }

    /**
     * @param Collection<int, TranslatedTag> $translations
     */
    public function setTranslations(Collection $translations): self
    {
        $this->translations = $translations;

        return $this;
    }

    public function addPicture(Picture $picture): self
    {
        $this->pictures[] = $picture;

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        $this->pictures->removeElement($picture);

        return $this;
    }

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }
}
