<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pack
 *
 * @ORM\Table(name="pack")
 * @ORM\Entity(repositoryClass="App\Repository\PackRepository")
 */
class Pack
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(name="name", type="string", length=150)
     * @Assert\Length(min="2", max="150")
     */
    private string $name;

    /**
     * @ORM\Column(name="storagePath", type="string", length=255)
     */
    private string $storagePath;

    /**
     * @ORM\Column(name="status", type="integer")
     */
    private int $status;

    /**
     * @ORM\Column(name="views", type="integer", options={"unsigned"=true})
     */
    private int $views = 0;

    /**
     * @var Collection<int, Picture>
     *
     * @ORM\ManyToMany(targetEntity="Picture")
     */
    private Collection $pictures;

    /**
     * @var Collection<int, Tag>
     *
     * @ORM\ManyToMany(targetEntity="Tag")
     */
    private Collection $tags;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private ?UserInterface $creator;

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
     * @Assert\All({
     *     @Assert\File(
     *     mimeTypes={
     *         "application/zip",
     *         "application/x-rar-compressed",
     *         "application/x-rar",
     *         "application/gzip",
     *         "application/x-tar",
     *         "image/gif",
     *         "image/png",
     *         "image/jpeg",
     *         "image/webp"
     *      }
     *     )
     * })
     *
     * @var array|UploadedFile[]
     */
    private array $files;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = '';
        $this->pictures = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
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

    public function getViews(): int
    {
        return $this->views;
    }

    public function incrementViews(): self
    {
        $this->views++;

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

    public function removePicture(Picture $picture): self
    {
        $this->pictures->removeElement($picture);

        return $this;
    }

}
