<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ActivityUniqueName;
use App\Enum\PointsTarget;
use App\Repository\ActivityTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActivityTypeRepository::class)]
class ActivityType
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
    private int $basePoints = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $allowsMultipleParticipants = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'string', enumType: PointsTarget::class, length: 20)]
    private PointsTarget $pointsTarget = PointsTarget::BOTH;

    #[ORM\Column(type: 'string', enumType: ActivityUniqueName::class, length: 50, nullable: true, unique: true)]
    private ?ActivityUniqueName $uniqueName = null;

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getBasePoints(): int { return $this->basePoints; }
    public function setBasePoints(int $basePoints): static { $this->basePoints = $basePoints; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function isAllowsMultipleParticipants(): bool { return $this->allowsMultipleParticipants; }
    public function setAllowsMultipleParticipants(bool $allowsMultipleParticipants): static { $this->allowsMultipleParticipants = $allowsMultipleParticipants; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getPointsTarget(): PointsTarget { return $this->pointsTarget; }
    public function setPointsTarget(PointsTarget $pointsTarget): static { $this->pointsTarget = $pointsTarget; return $this; }

    public function isPlanetOnly(): bool { return $this->pointsTarget === PointsTarget::PLANET_ONLY; }

    public function getUniqueName(): ?ActivityUniqueName { return $this->uniqueName; }
    public function setUniqueName(?ActivityUniqueName $uniqueName): static { $this->uniqueName = $uniqueName; return $this; }

    public function __toString(): string { return $this->name . ' (' . $this->basePoints . ' pts)'; }
}
