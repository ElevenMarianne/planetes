<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
class Season
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = false;

    #[ORM\OneToMany(targetEntity: PlanetSeasonPoints::class, mappedBy: 'season', cascade: ['persist', 'remove'])]
    private Collection $planetPoints;

    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'season')]
    private Collection $activities;

    #[ORM\OneToMany(targetEntity: AstronautTrophy::class, mappedBy: 'season')]
    private Collection $astronautTrophies;

    #[ORM\OneToMany(targetEntity: PlanetTrophy::class, mappedBy: 'season')]
    private Collection $planetTrophies;

    public function __construct()
    {
        $this->planetPoints = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->astronautTrophies = new ArrayCollection();
        $this->planetTrophies = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getStartDate(): ?\DateTimeInterface { return $this->startDate; }
    public function setStartDate(?\DateTimeInterface $startDate): static { $this->startDate = $startDate; return $this; }

    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
    public function setEndDate(?\DateTimeInterface $endDate): static { $this->endDate = $endDate; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    /** @return Collection<int, PlanetSeasonPoints> */
    public function getPlanetPoints(): Collection { return $this->planetPoints; }

    /** @return Collection<int, Activity> */
    public function getActivities(): Collection { return $this->activities; }

    /** @return Collection<int, AstronautTrophy> */
    public function getAstronautTrophies(): Collection { return $this->astronautTrophies; }

    /** @return Collection<int, PlanetTrophy> */
    public function getPlanetTrophies(): Collection { return $this->planetTrophies; }

    public function __toString(): string { return $this->name; }
}
