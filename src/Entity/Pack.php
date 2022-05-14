<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\Status;
use App\Repository\PackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'pack')]
#[ORM\Entity(repositoryClass: PackRepository::class)]
#[ORM\Index(columns: ['name'], flags: ['fulltext'])]
class Pack
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['overview', 'export'])]
    private string $id;

    #[ORM\Column(name: 'name', type: 'string', length: 150)]
    #[Assert\Length(min: 2, max: 150)]
    #[Groups(['overview', 'export'])]
    private string $name;

    #[ORM\Column(name: 'storagePath', type: 'string', length: 255)]
    private string $storagePath;

    #[ORM\Column(name: 'status', type: 'integer')]
    private int $status;

    #[ORM\Column(name: 'views', type: 'integer', options: ['unsigned' => true])]
    #[Groups(['export'])]
    private int $views = 0;

    /**
     * @var Collection<int, Picture>
     */
    #[ORM\ManyToMany(targetEntity: Picture::class, inversedBy: 'packs')]
    #[Groups(['export'])]
    private Collection $pictures;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[Groups(['export'])]
    private Collection $tags;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['export'])]
    private ?UserInterface $creator;

    #[ORM\Column(name: 'created', type: 'datetime')]
    #[Groups(['export'])]
    private \DateTime $created;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated', type: 'datetime')]
    #[Groups(['export'])]
    private \DateTime $updated;

    #[ORM\Column(name: 'deletedAt', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $deletedAt;

    /**
     * @var array|UploadedFile[]
     */
    #[Assert\All(
        [
            new Assert\File(
                mimeTypes: [
                    'application/zip',
                    'application/x-rar-compressed',
                    'application/x-rar',
                    'application/gzip',
                    'application/x-tar',
                    'image/gif',
                    'image/png',
                    'image/jpeg',
                    'image/webp'
                ]
            )
        ]
    )]
    private array $files;

    /**
     * @var Collection<int, GalleryViewHistory>
     */
    #[ORM\OneToMany(mappedBy: 'gallery', targetEntity: GalleryViewHistory::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $viewHistory;


    public function __construct()
    {
        $this->name = '';
        $this->pictures = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->viewHistory = new ArrayCollection();
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): string
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

    public function getViews(): int
    {
        return $this->views;
    }

    public function incrementViews(User $user): self
    {
        $this->views++;

        $this->addViewHistory(
            (new GalleryViewHistory())
            ->setUser($user)
        );

        return $this;
    }

    public function addTag(Tag $tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function setCreator(UserInterface $creator = null): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getCreator(): ?UserInterface
    {
        return $this->creator;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    public function setUpdated(\DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return array|UploadedFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param array|UploadedFile[] $files
     */
    public function setFiles(array $files): self
    {
        $this->files = $files;

        return $this;
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath): self
    {
        $this->storagePath = $storagePath;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

	#[Groups(['export'])]
	#[SerializedName('status')]
	public function getStatusName(): string
	{
		return Status::toName($this->status);
	}

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        $this->pictures[] = $picture;

        return $this;
    }

    /**
     * @param Picture[] $pictures
     *
     * @return $this
     */
    public function setPictures(array $pictures): self
    {
        $this->pictures = new ArrayCollection($pictures);

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        $this->pictures->removeElement($picture);

        return $this;
    }

    #[Groups(['overview'])]
    public function getThumbnail(): string
    {
        /** @var Picture|false $firstPicture */
        $firstPicture = $this->pictures->first();

        if ($firstPicture === false) {
            return '';
        }

        return $firstPicture->getId();
    }

    /**
     * @return Collection<int, GalleryViewHistory>
     */
    public function getViewHistory(): Collection
    {
        return $this->viewHistory;
    }

    public function addViewHistory(GalleryViewHistory $viewHistory): self
    {
        if (!$this->viewHistory->contains($viewHistory)) {
            $this->viewHistory[] = $viewHistory;
            $viewHistory->setGallery($this);
        }

        return $this;
    }

    public function removeViewHistory(GalleryViewHistory $viewHistory): self
    {
        $this->viewHistory->removeElement($viewHistory);

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
