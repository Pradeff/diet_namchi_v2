<?php

namespace App\Entity;

use App\Repository\VcourseRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VcourseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Vcourse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title is required.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Title must be at least {{ limit }} characters long.",
        maxMessage: "Title cannot be longer than {{ limit }} characters."
    )]
    private string $title;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ["title"])]
    private ?string $slug = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Description is required.")]
    private string $description = '';

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $images = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;


    // ------------------
    // Getters & Setters
    // ------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images ?? [];
    }

    public function setImages(?array $images): self
    {
        $this->images = $images;
        return $this;
    }

    public function addImage(string $imagePath): self
    {
        if (!in_array($imagePath, $this->images ?? [])) {
            $this->images[] = $imagePath;
        }

        return $this;
    }

    public function removeImage(string $imagePath): self
    {
        $this->images = array_filter(
            $this->images ?? [],
            fn($image) => $image !== $imagePath
        );

        $this->images = array_values($this->images);

        return $this;
    }

    public function hasImage(string $imagePath): bool
    {
        return in_array($imagePath, $this->images ?? []);
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): self
    {
        $this->coverImage = $coverImage;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    // ------------------
    // Lifecycle Callbacks
    // ------------------

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable("now");
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable("now");
    }


    // ------------------
    // Helper
    // ------------------

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
