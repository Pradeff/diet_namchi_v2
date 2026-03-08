<?php

namespace App\Entity;

use App\Repository\VaboutRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VaboutRepository::class)]
class Vabout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $subtitle = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description1 = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description2 = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description3 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $cover_image = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $video = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $images = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updated_at = null;

    // -- Getters & Setters --
    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getSubtitle(): ?string { return $this->subtitle; }
    public function setSubtitle(?string $subtitle): self { $this->subtitle = $subtitle; return $this; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }

    public function getDescription1(): ?string { return $this->description1; }
    public function setDescription1(?string $description1): self { $this->description1 = $description1; return $this; }

    public function getDescription2(): ?string { return $this->description2; }
    public function setDescription2(?string $description2): self { $this->description2 = $description2; return $this; }

    public function getDescription3(): ?string { return $this->description3; }
    public function setDescription3(?string $description3): self { $this->description3 = $description3; return $this; }

    public function getCoverImage(): ?string { return $this->cover_image; }
    public function setCoverImage(?string $cover_image): self { $this->cover_image = $cover_image; return $this; }

    public function getVideo(): ?string { return $this->video; }
    public function setVideo(?string $video): self { $this->video = $video; return $this; }

    public function getImages(): array { return $this->images ?? []; }
    public function setImages(?array $images): self { $this->images = $images ?? []; return $this; }

    public function addImage(string $filename, string $title = ''): self {
        foreach ($this->images ?? [] as $existing) {
            if ($existing['filename'] === $filename) { return $this; }
        }
        $this->images[] = ['filename' => $filename, 'title' => $title];
        return $this;
    }
    public function removeImage(string $filename): self {
        $this->images = array_values(array_filter(
            $this->images ?? [],
            fn($img) => $img['filename'] !== $filename
        ));
        return $this;
    }
    public function hasImage(string $filename): bool {
        foreach ($this->images ?? [] as $img) {
            if ($img['filename'] === $filename) { return true; }
        }
        return false;
    }
    public function updateImageTitle(string $filename, string $title): self {
        foreach ($this->images as &$img) {
            if ($img['filename'] === $filename) { $img['title'] = $title; break; }
        }
        return $this;
    }
    public function getImageFilenames(): array {
        return array_column($this->images ?? [], 'filename');
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->created_at; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static { $this->created_at = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updated_at; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static { $this->updated_at = $updatedAt; return $this; }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->created_at = new \DateTimeImmutable("now"); }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->updated_at = new \DateTimeImmutable("now"); }

    public function __toString(): string { return $this->title; }
}
