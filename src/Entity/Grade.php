<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GradeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GradeRepository::class)]
#[ORM\Index(fields: ['minPoints'], name: 'idx_grade_min_points')]
class Grade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(length: 100, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero]
    private int $minPoints = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getMinPoints(): int { return $this->minPoints; }
    public function setMinPoints(int $minPoints): static { $this->minPoints = $minPoints; return $this; }

    public function getIcon(): ?string { return $this->icon; }
    public function setIcon(?string $icon): static { $this->icon = $icon; return $this; }

    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }

    public function __toString(): string { return $this->name; }
}
