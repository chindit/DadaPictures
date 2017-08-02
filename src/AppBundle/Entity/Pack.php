<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Pack
 *
 * @ORM\Table(name="pack")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PackRepository")
 */
class Pack
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=150)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="storagePath", type="string", length=255)
     */
    private $storagePath;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var PersistentCollection|Picture[]
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Picture", mappedBy="pack")
     */
    private $pictures;

    /**
     * @var PersistentCollection|Tag[]
     *
     * @ORM\ManyToMany(targetEntity="Tag")
     */
    private $tags;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $creator;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

    /**
     * @var File
     */
    private $file;

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

    /**
     * Get id
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Pack
     */
    public function setName(string $name) : Pack
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Add tag
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return Pack
     */
    public function addTag(Tag $tag) : Pack
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \AppBundle\Entity\Tag $tag
     * @return Pack
     */
    public function removeTag(Tag $tag) : Pack
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * Get tags
     *
     * @return PersistentCollection|Tag[]
     */
    public function getTags() : PersistentCollection
    {
        return $this->tags;
    }

    /**
     * Set creator
     *
     * @param \AppBundle\Entity\User $creator
     *
     * @return Pack
     */
    public function setCreator(User $creator = null) : Pack
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \AppBundle\Entity\User
     */
    public function getCreator() : User
    {
        return $this->creator;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() : \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     * @return Pack
     */
    public function setCreated(\DateTime $created) : Pack
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated() : \DateTime
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     * @return Pack
     */
    public function setUpdated(\DateTime $updated)  : Pack
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return File
     */
    public function getFile() : ?File
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     * @return Pack
     */
    public function setFile(File $file) : Pack
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    /**
     * @param string $storagePath
     * @return Pack
     */
    public function setStoragePath(string $storagePath): Pack
    {
        $this->storagePath = $storagePath;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Pack
     */
    public function setStatus(int $status): Pack
    {
        $this->status = $status;
        return $this;
    }


    /**
     * Set pictures
     *
     * @param \AppBundle\Entity\Picture $pictures
     *
     * @return Pack
     */
    public function setPictures(Picture $pictures = null) : Pack
    {
        $this->pictures = $pictures;

        return $this;
    }

    /**
     * Get pictures
     *
     * @return PersistentCollection|\AppBundle\Entity\Picture[]
     */
    public function getPictures() : PersistentCollection
    {
        return $this->pictures;
    }

    /**
     * Add picture
     *
     * @param \AppBundle\Entity\Picture $picture
     *
     * @return Pack
     */
    public function addPicture(Picture $picture) : Pack
    {
        $this->pictures[] = $picture;

        return $this;
    }

    /**
     * Remove picture
     *
     * @param \AppBundle\Entity\Picture $picture
     * @return Pack
     */
    public function removePicture(Picture $picture) : Pack
    {
        $this->pictures->removeElement($picture);

        return $this;
    }
}
