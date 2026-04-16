<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\Index(fields: ['season'], name: 'idx_activity_season')]
class Activity
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Points finaux après calcul de tous les bonus.
     * Stockés à la création — ne pas recalculer à la lecture.
     */
    #[ORM\Column(type: 'integer')]
    private int $points = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne(targetEntity: ActivityType::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?ActivityType $type = null;

    #[ORM\ManyToOne(targetEntity: Season::class, inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Season $season = null;

    #[ORM\ManyToOne(targetEntity: Planet::class, inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Planet $planet = null;

    #[ORM\ManyToMany(targetEntity: Astronaut::class, inversedBy: 'activities')]
    #[ORM\JoinTable(name: 'activity_astronaut')]
    private Collection $astronauts;

    public function __construct()
    {
        $this->astronauts = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getPoints(): int { return $this->points; }
    public function setPoints(int $points): static { $this->points = $points; return $this; }

    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): static { $this->note = $note; return $this; }

    public function getType(): ?ActivityType { return $this->type; }
    public function setType(?ActivityType $type): static { $this->type = $type; return $this; }

    public function getSeason(): ?Season { return $this->season; }
    public function setSeason(?Season $season): static { $this->season = $season; return $this; }

    public function getPlanet(): ?Planet { return $this->planet; }
    public function setPlanet(?Planet $planet): static { $this->planet = $planet; return $this; }

    /** @return Collection<int, Astronaut> */
    public function getAstronauts(): Collection { return $this->astronauts; }

    public function addAstronaut(Astronaut $astronaut): static
    {
        if (!$this->astronauts->contains($astronaut)) {
            $this->astronauts->add($astronaut);
        }
        return $this;
    }

    public function removeAstronaut(Astronaut $astronaut): static
    {
        $this->astronauts->removeElement($astronaut);
        return $this;
    }
}
