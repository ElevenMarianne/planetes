<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TrophyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrophyRepository::class)]
class Trophy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(length: 100, unique: true, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToMany(targetEntity: AstronautTrophy::class, mappedBy: 'trophy', cascade: ['remove'])]
    private Collection $astronautTrophies;

    #[ORM\OneToMany(targetEntity: PlanetTrophy::class, mappedBy: 'trophy', cascade: ['remove'])]
    private Collection $planetTrophies;

    public function __construct()
    {
        $this->astronautTrophies = new ArrayCollection();
        $this->planetTrophies = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(?string $slug): static { $this->slug = $slug; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }

    /** @return Collection<int, AstronautTrophy> */
    public function getAstronautTrophies(): Collection { return $this->astronautTrophies; }

    /** @return Collection<int, PlanetTrophy> */
    public function getPlanetTrophies(): Collection { return $this->planetTrophies; }

    public function __toString(): string { return $this->name; }
}
