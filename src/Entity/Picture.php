<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Picture
 *
 * @ORM\Table(name="picture")
 * @ORM\Entity(repositoryClass="App\Repository\PictureRepository")
 *
 */
class Picture
{
    public const STATUS_OK = 1;
    public const STATUS_TEMP = 2;
    public const STATUS_ERROR = 3;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(name="name", type="string", length=150)
     */
    private string $name;

    /**
     * @ORM\Column(name="filename", type="string", length=255, unique=true)
     */
    private string $filename;

    /**
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private ?int $height;

    /**
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private ?int $width;

    /**
     * @ORM\Column(name="weight", type="integer", nullable=true)
     */
    private ?int $weight;

    /**
     * @ORM\Column(name="mime", type="string", length=50)
     */
    private string $mime;

    /**
     * @ORM\Column(name="sha1", type="string", length=40, nullable=true)
     */
    private string $sha1sum;

    /**
     * @ORM\Column(name="properties", type="text", nullable=true)
     */
    private string $properties;

    /**
     * @ORM\Column(name="status", type="integer")
     */
    private int $status;

    /**
     * @ORM\Column(name="statusInfo", type="string", length=150)
     */
    private string $statusInfo;

    /**
     * @ORM\Column(name="views", type="integer", options={"unsigned"=true})
     */
    private int $views = 0;

    /**
     * @ORM\Column(name="thumbnail", type="string", unique=true, nullable=true)
     */
    private ?string $thumbnail;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private UserInterface $creator;

    /**
     * @ORM\Column(name="created", type="datetime")
     */
    private \DateTime $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private \DateTime $updated;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="pictures")
     * @var Collection<int, Tag> $tags
     */
    private Collection $tags;


    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->mime = 'error';
        $this->filename = '';
    }

    public function getId(): int
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

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setMime(string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function incrementViews(): self
    {
        $this->views++;

        return $this;
    }

    public function getThumbnail(): string
    {
        return $this->thumbnail ?? $this->getFilename();
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @param array|string[] $properties
     */
    public function setProperties(array $properties): self
    {
        $encoded = json_encode($properties);
        if ($encoded === false)
        {
            return $this;
        }
        $this->properties = $encoded;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getProperties(): ?array
    {
        $decoded = json_decode($this->properties, true)

        return is_array($decoded) ? $decoded : null;
    }

    public function setCreator(UserInterface $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getCreator(): UserInterface
    {
        return $this->creator;
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
    public function getTags(): Collection
    {
        return $this->tags;
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

    public function getSha1sum(): string
    {
        return $this->sha1sum;
    }

    public function setSha1sum(string $sha1sum): self
    {
        $this->sha1sum = $sha1sum;
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

    public function getStatusInfo(): string
    {
        return $this->statusInfo;
    }

    public function setStatusInfo(string $statusInfo): self
    {
        $this->statusInfo = $statusInfo;

        return $this;
    }
}
