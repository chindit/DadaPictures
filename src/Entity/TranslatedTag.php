<?php

namespace App\Entity;

use App\Model\Languages;
use App\Repository\TranslatedTagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranslatedTagRepository::class)]
class TranslatedTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\Column(type: 'string', length: 2)]
    private string $language;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

	#[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'translations')]
	#[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id')]
	private Tag $tag;

	public function __construct(string $language = Languages::EN, string $name = '')
	{
		$this->language = $language;
		$this->name = $name;
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

	public function getTag(): Tag
	{
		return $this->tag;
	}

	public function setTag(Tag $tag): self
	{
		$this->tag = $tag;

		return $this;
	}
}
