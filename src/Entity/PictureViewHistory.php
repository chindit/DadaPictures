<?php

namespace App\Entity;

use App\Repository\PictureViewHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PictureViewHistoryRepository::class)]
class PictureViewHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Picture::class, inversedBy: 'viewHistory')]
    #[ORM\JoinColumn(nullable: false)]
    private $picture;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'pictureViewHistory')]
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

    public function getPicture(): ?Picture
    {
        return $this->picture;
    }

    public function setPicture(?Picture $picture): self
    {
        $this->picture = $picture;

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
