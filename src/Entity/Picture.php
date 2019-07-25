<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Picture
 *
 * @ORM\Table(name="picture")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PictureRepository")
 */
class Picture
{
    const STATUS_OK = 1;
    const STATUS_TEMP = 2;
    const STATUS_ERROR = 3;

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
     * @ORM\Column(name="filename", type="string", length=255, unique=true)
     */
    private $filename;

    /**
     * @var int
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * @var int
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * @var int
     *
     * @ORM\Column(name="weight", type="integer", nullable=true)
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="mime", type="string", length=50)
     */
    private $mime;

    /**
     * @var string
     *
     * @ORM\Column(name="sha1", type="string", length=40, nullable=true)
     */
    private $sha1sum;

    /**
     * @var string
     *
     * @ORM\Column(name="md5", type="string", length=32, nullable=true)
     */
    private $md5sum;

    /**
     * @var array
     *
     * @ORM\Column(name="properties", type="array", nullable=true)
     */
    private $properties;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="statusInfo", type="string", length=150)
     */
    private $statusInfo;

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
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="pictures")
     */
    private $tags;

    /**
     * @var Pack
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pack", inversedBy="pictures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pack;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->mime = 'error';
        $this->filename = '';
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
     * @return Picture
     */
    public function setName(string $name) : Picture
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
     * Set filename
     *
     * @param string $filename
     *
     * @return Picture
     */
    public function setFilename(string $filename) : Picture
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename() : string
    {
        return $this->filename;
    }

    /**
     * Set height
     *
     * @param integer $height
     *
     * @return Picture
     */
    public function setHeight(int $height) : Picture
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight() : int
    {
        return $this->height;
    }

    /**
     * Set width
     *
     * @param integer $width
     *
     * @return Picture
     */
    public function setWidth(int $width) : Picture
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return int
     */
    public function getWidth() : int
    {
        return $this->width;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     *
     * @return Picture
     */
    public function setWeight(int $weight) : Picture
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return int
     */
    public function getWeight() : int
    {
        return $this->weight;
    }

    /**
     * Set mime
     *
     * @param string $mime
     *
     * @return Picture
     */
    public function setMime(string $mime) : Picture
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * Get mime
     *
     * @return string
     */
    public function getMime() : string
    {
        return $this->mime;
    }

    /**
     * Set properties
     *
     * @param array $properties
     *
     * @return Picture
     */
    public function setProperties(array $properties) : Picture
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Get properties
     *
     * @return array
     */
    public function getProperties() : ?array
    {
        return $this->properties;
    }

    /**
     * Set creator
     *
     * @param \AppBundle\Entity\User $creator
     *
     * @return Picture
     */
    public function setCreator(User $creator = null) : Picture
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
     * Add tag
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return Picture
     */
    public function addTag(Tag $tag) : Picture
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \AppBundle\Entity\Tag $tag
     */
    public function removeTag(Tag $tag) : Picture
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * Get tags
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
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
     * @return Picture
     */
    public function setCreated(\DateTime $created) : Picture
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
     * @return Picture
     */
    public function setUpdated(\DateTime $updated) : Picture
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return string
     */
    public function getSha1sum(): string
    {
        return $this->sha1sum;
    }

    /**
     * @param string $sha1sum
     * @return Picture
     */
    public function setSha1sum(string $sha1sum): Picture
    {
        $this->sha1sum = $sha1sum;
        return $this;
    }

    /**
     * @return string
     */
    public function getMd5sum(): string
    {
        return $this->md5sum;
    }

    /**
     * @param string $md5sum
     * @return Picture
     */
    public function setMd5sum(string $md5sum): Picture
    {
        $this->md5sum = $md5sum;
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
     * @return Picture
     */
    public function setStatus(int $status): Picture
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusInfo(): string
    {
        return $this->statusInfo;
    }

    /**
     * @param string $statusInfo
     * @return Picture
     */
    public function setStatusInfo(string $statusInfo): Picture
    {
        $this->statusInfo = $statusInfo;
        return $this;
    }


    /**
     * Set pack
     *
     * @param \AppBundle\Entity\Pack $pack
     *
     * @return Picture
     */
    public function setPack(Pack $pack) : Picture
    {
        $this->pack = $pack;

        return $this;
    }

    /**
     * Get pack
     *
     * @return \AppBundle\Entity\Pack
     */
    public function getPack() : Pack
    {
        return $this->pack;
    }
}