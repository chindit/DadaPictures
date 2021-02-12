<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\ManyToMany(targetEntity="App\Entity\Picture", mappedBy="tags")
     *
     * @var Collection<int, Picture> $pictures
     */
    private Collection $pictures;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function getName() : string
    {
        return $this->name;
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
