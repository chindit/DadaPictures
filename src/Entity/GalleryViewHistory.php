<?php

namespace App\Entity;

use App\Repository\GalleryViewHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GalleryViewHistoryRepository::class)]
class GalleryViewHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Pack::class, inversedBy: 'viewHistory')]
    #[ORM\JoinColumn(nullable: false)]
    private $gallery;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'galleryViewHistory')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'datetime_immutable')]
    private $viewedAt;

    public function __construct()
    {
        $this->viewedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGallery(): ?Pack
    {
        return $this->gallery;
    }

    public function setGallery(?Pack $gallery): self
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getViewedAt(): ?\DateTimeImmutable
    {
        return $this->viewedAt;
    }

    public function setViewedAt(\DateTimeImmutable $viewedAt): self
    {
        $this->viewedAt = $viewedAt;

        return $this;
    }
}
