<?php

namespace App\Entity;

use App\Repository\BannedPictureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BannedPictureRepository::class)]
class BannedPicture
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $sha1;

    #[ORM\Column(name: 'created', type: 'datetime')]
    private \DateTime $created;

    public function __construct(string $sha1)
    {
        $this->sha1 = $sha1;
        $this->created = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setSha1(string $sha1): self
    {
        $this->sha1 = $sha1;

        return $this;
    }

    public function getSha1(): string
    {
        return $this->sha1;
    }

    public function getCreationDate(): \DateTime
    {
        return $this->created;
    }
}
