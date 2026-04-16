<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EventParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventParticipantRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_EVENT_ASTRONAUT', fields: ['event', 'astronaut'])]
class EventParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $registeredAt;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(targetEntity: Astronaut::class, inversedBy: 'eventParticipations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Astronaut $astronaut = null;

    public function __construct()
    {
        $this->registeredAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getRegisteredAt(): \DateTimeInterface { return $this->registeredAt; }

    public function getEvent(): ?Event { return $this->event; }
    public function setEvent(?Event $event): static { $this->event = $event; return $this; }

    public function getAstronaut(): ?Astronaut { return $this->astronaut; }
    public function setAstronaut(?Astronaut $astronaut): static { $this->astronaut = $astronaut; return $this; }
}
