<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Picture
 *
 * @ORM\Table(name="picture")
 * @ORM\Entity(repositoryClass="App\Repository\PictureRepository")
 *
 */
class Picture
{
    const STATUS_OK = 1;
    const STATUS_TEMP = 2;
    const STATUS_ERROR = 3;

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
     * @ORM\Column(name="properties", type="array", nullable=true)
     */
    private array $properties;

    /**
     * @ORM\Column(name="status", type="integer")
     */
    private int $status;

    /**
     * @ORM\Column(name="statusInfo", type="string", length=150)
     */
    private string $statusInfo;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private User $creator;

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

    public function getId() : int
    {
        return $this->id;
    }

    public function setName(string $name): Picture
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setFilename(string $filename): Picture
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setHeight(int $height): Picture
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setWidth(int $width): Picture
    {
        $this->width = $width;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWeight(int $weight): Picture
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setMime(string $mime): Picture
    {
        $this->mime = $mime;

        return $this;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function setProperties(array $properties): Picture
    {
        $this->properties = $properties;

        return $this;
    }

    public function getProperties(): ?array
    {
        return $this->properties;
    }

    public function setCreator(User $creator = null): Picture
    {
        $this->creator = $creator;

        return $this;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function addTag(Tag $tag): Picture
    {
        $this->tags[] = $tag;

        return $this;
    }

    public function removeTag(Tag $tag) : Picture
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function getCreated() : \DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): Picture
    {
        $this->created = $created;
        return $this;
    }

    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    public function setUpdated(\DateTime $updated): Picture
    {
        $this->updated = $updated;

        return $this;
    }

    public function getSha1sum(): string
    {
        return $this->sha1sum;
    }

    public function setSha1sum(string $sha1sum): Picture
    {
        $this->sha1sum = $sha1sum;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): Picture
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusInfo(): string
    {
        return $this->statusInfo;
    }

    public function setStatusInfo(string $statusInfo): Picture
    {
        $this->statusInfo = $statusInfo;
        return $this;
    }
}
