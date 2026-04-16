<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AstronautTrophyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AstronautTrophyRepository::class)]
class AstronautTrophy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $awardedAt;

    #[ORM\ManyToOne(targetEntity: Astronaut::class, inversedBy: 'trophies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Astronaut $astronaut = null;

    #[ORM\ManyToOne(targetEntity: Trophy::class, inversedBy: 'astronautTrophies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trophy $trophy = null;

    #[ORM\ManyToOne(targetEntity: Season::class, inversedBy: 'astronautTrophies')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Season $season = null;

    public function __construct()
    {
        $this->awardedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getAwardedAt(): \DateTimeInterface { return $this->awardedAt; }
    public function setAwardedAt(\DateTimeInterface $awardedAt): static { $this->awardedAt = $awardedAt; return $this; }

    public function getAstronaut(): ?Astronaut { return $this->astronaut; }
    public function setAstronaut(?Astronaut $astronaut): static { $this->astronaut = $astronaut; return $this; }

    public function getTrophy(): ?Trophy { return $this->trophy; }
    public function setTrophy(?Trophy $trophy): static { $this->trophy = $trophy; return $this; }

    public function getSeason(): ?Season { return $this->season; }
    public function setSeason(?Season $season): static { $this->season = $season; return $this; }
}
