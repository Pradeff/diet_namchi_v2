<?php

namespace App\Entity;

use App\Repository\VgalleryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VgalleryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Vgallery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $images = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->images = [];
    }

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

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
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

    public function addImage(string $filename, string $title = ''): self
    {
        $imageData = ['filename' => $filename, 'title' => $title];

        // Check if image already exists (by filename)
        foreach ($this->images ?? [] as $existing) {
            if ($existing['filename'] === $filename) {
                return $this;
            }
        }

        $this->images[] = $imageData;
        return $this;
    }

    public function removeImage(string $filename): self
    {
        $this->images = array_filter($this->images ?? [], function($image) use ($filename) {
            return $image['filename'] !== $filename;
        });
        $this->images = array_values($this->images);
        return $this;
    }

    public function hasImage(string $filename): bool
    {
        foreach ($this->images ?? [] as $image) {
            if ($image['filename'] === $filename) {
                return true;
            }
        }
        return false;
    }

    public function updateImageTitle(string $filename, string $title): self
    {
        foreach ($this->images as &$image) {
            if ($image['filename'] === $filename) {
                $image['title'] = $title;
                break;
            }
        }
        return $this;
    }

    public function getImageFilenames(): array
    {
        return array_column($this->images ?? [], 'filename');
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

    #[ORM\PrePersist()]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable("now");
    }

    #[ORM\PreUpdate()]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable("now");
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
