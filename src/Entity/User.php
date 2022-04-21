<?php

namespace App\Entity;

use App\Model\Languages;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(['export'])]
    private string $username;

    /**
     * @var string[] $roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles = ["ROLE_USER"];

    #[ORM\Column(type: 'string', length: 2)]
    private ?string $language = Languages::EN;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', length: 125, nullable: true, unique: true)]
    private ?string $email;

	#[ORM\Column(name: 'created', type: 'datetime')]
    private \DateTime $created;

	#[ORM\Column(name: 'last_login', type: 'datetime')]
    private \DateTime $lastLogin;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: GalleryViewHistory::class, orphanRemoval: true)]
    private $galleryViewHistory;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PictureViewHistory::class, orphanRemoval: true)]
    private $pictureViewHistory;

	public function __construct()
                                    	{
                                    		$this->created = new \DateTime();
                                    		$this->lastLogin = new \DateTime();
                                      $this->galleryViewHistory = new ArrayCollection();
                                      $this->pictureViewHistory = new ArrayCollection();
                                    	}

	public function getId(): ?int
                                        {
                                            return $this->id;
                                        }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array|string[] $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language ?? Languages::EN;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): string
    {
        return '';
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

	public function updateLastLogin(): self
    {
        $this->lastLogin = new \DateTime();

        return $this;
    }

    /**
     * @return Collection<int, GalleryViewHistory>
     */
    public function getGalleryViewHistory(): Collection
    {
        return $this->galleryViewHistory;
    }

    public function addGalleryViewHistory(GalleryViewHistory $galleryViewHistory): self
    {
        if (!$this->galleryViewHistory->contains($galleryViewHistory)) {
            $this->galleryViewHistory[] = $galleryViewHistory;
            $galleryViewHistory->setUser($this);
        }

        return $this;
    }

    public function removeGalleryViewHistory(GalleryViewHistory $galleryViewHistory): self
    {
        if ($this->galleryViewHistory->removeElement($galleryViewHistory)) {
            // set the owning side to null (unless already changed)
            if ($galleryViewHistory->getUser() === $this) {
                $galleryViewHistory->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PictureViewHistory>
     */
    public function getPictureViewHistory(): Collection
    {
        return $this->pictureViewHistory;
    }

    public function addPictureViewHistory(PictureViewHistory $pictureViewHistory): self
    {
        if (!$this->pictureViewHistory->contains($pictureViewHistory)) {
            $this->pictureViewHistory[] = $pictureViewHistory;
            $pictureViewHistory->setUser($this);
        }

        return $this;
    }

    public function removePictureViewHistory(PictureViewHistory $pictureViewHistory): self
    {
        if ($this->pictureViewHistory->removeElement($pictureViewHistory)) {
            // set the owning side to null (unless already changed)
            if ($pictureViewHistory->getUser() === $this) {
                $pictureViewHistory->setUser(null);
            }
        }

        return $this;
    }
}
