<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PlanetSeasonPointsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanetSeasonPointsRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_PLANET_SEASON', fields: ['planet', 'season'])]
#[ORM\Index(fields: ['season', 'totalPoints'], name: 'idx_psp_season_points')]
class PlanetSeasonPoints
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $totalPoints = 0;

    #[ORM\ManyToOne(targetEntity: Planet::class, inversedBy: 'seasonPoints')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Planet $planet = null;

    #[ORM\ManyToOne(targetEntity: Season::class, inversedBy: 'planetPoints')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Season $season = null;

    public function getId(): ?int { return $this->id; }

    public function getTotalPoints(): int { return $this->totalPoints; }
    public function setTotalPoints(int $totalPoints): static { $this->totalPoints = $totalPoints; return $this; }
    public function addPoints(int $points): static { $this->totalPoints += $points; return $this; }
    public function subtractPoints(int $points): static { $this->totalPoints = max(0, $this->totalPoints - $points); return $this; }

    public function getPlanet(): ?Planet { return $this->planet; }
    public function setPlanet(?Planet $planet): static { $this->planet = $planet; return $this; }

    public function getSeason(): ?Season { return $this->season; }
    public function setSeason(?Season $season): static { $this->season = $season; return $this; }
}
