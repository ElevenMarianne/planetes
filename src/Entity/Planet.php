<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PlanetType;
use App\Repository\PlanetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlanetRepository::class)]
class Planet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name = '';

    #[ORM\Column(length: 100, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private string $slug = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $artwork = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $mantra = null;

    #[ORM\Column(length: 7)]
    #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/')]
    private string $color = '#6366f1';

    #[ORM\Column(type: 'string', enumType: PlanetType::class)]
    private PlanetType $type = PlanetType::MAIN;

    #[ORM\OneToMany(targetEntity: Astronaut::class, mappedBy: 'planet')]
    private Collection $astronauts;

    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'planet')]
    private Collection $activities;

    #[ORM\OneToMany(targetEntity: PlanetSeasonPoints::class, mappedBy: 'planet', cascade: ['persist', 'remove'])]
    private Collection $seasonPoints;

    #[ORM\OneToMany(targetEntity: PlanetTrophy::class, mappedBy: 'planet', cascade: ['persist', 'remove'])]
    private Collection $trophies;

    public function __construct()
    {
        $this->astronauts = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->seasonPoints = new ArrayCollection();
        $this->trophies = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): static { $this->photo = $photo; return $this; }

    public function getArtwork(): ?string { return $this->artwork; }
    public function setArtwork(?string $artwork): static { $this->artwork = $artwork; return $this; }

    public function getMantra(): ?string { return $this->mantra; }
    public function setMantra(?string $mantra): static { $this->mantra = $mantra; return $this; }

    public function getColor(): string { return $this->color; }
    public function setColor(string $color): static { $this->color = $color; return $this; }

    public function getType(): PlanetType { return $this->type; }
    public function setType(PlanetType $type): static { $this->type = $type; return $this; }

    public function isCompetitor(): bool { return $this->type->isCompetitor(); }

    /** @return Collection<int, Astronaut> */
    public function getAstronauts(): Collection { return $this->astronauts; }

    /** @return Collection<int, Activity> */
    public function getActivities(): Collection { return $this->activities; }

    /** @return Collection<int, PlanetSeasonPoints> */
    public function getSeasonPoints(): Collection { return $this->seasonPoints; }

    /** @return Collection<int, PlanetTrophy> */
    public function getTrophies(): Collection { return $this->trophies; }

    public function __toString(): string { return $this->name; }
}
