<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Squad;
use App\Repository\AstronautRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AstronautRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_GOOGLE_ID', fields: ['googleId'])]
#[ORM\Index(fields: ['lastName', 'firstName'], name: 'idx_astronaut_name')]
class Astronaut implements UserInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $firstName = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $lastName = '';

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'astronauts')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Client $client = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $hobbies = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $googleId = null;

    #[ORM\Column(type: 'integer')]
    private int $totalPoints = 0;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'string', enumType: Squad::class, length: 20)]
    private Squad $squad = Squad::PARIS;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $arrivedAt = null;

    #[ORM\ManyToOne(targetEntity: Planet::class, inversedBy: 'astronauts')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Planet $planet = null;

    #[ORM\ManyToMany(targetEntity: Activity::class, mappedBy: 'astronauts')]
    private Collection $activities;

    #[ORM\OneToMany(targetEntity: AstronautTrophy::class, mappedBy: 'astronaut', cascade: ['persist', 'remove'])]
    private Collection $trophies;

    #[ORM\OneToMany(targetEntity: EventParticipant::class, mappedBy: 'astronaut', cascade: ['persist', 'remove'])]
    private Collection $eventParticipations;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->trophies = new ArrayCollection();
        $this->eventParticipations = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $firstName): static { $this->firstName = $firstName; return $this; }

    public function getLastName(): string { return $this->lastName; }
    public function setLastName(string $lastName): static { $this->lastName = $lastName; return $this; }

    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): static { $this->photo = $photo; return $this; }

    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }

    public function getHobbies(): ?string { return $this->hobbies; }
    public function setHobbies(?string $hobbies): static { $this->hobbies = $hobbies; return $this; }

    public function getGoogleId(): ?string { return $this->googleId; }
    public function setGoogleId(?string $googleId): static { $this->googleId = $googleId; return $this; }

    public function getTotalPoints(): int { return $this->totalPoints; }
    public function setTotalPoints(int $totalPoints): static { $this->totalPoints = $totalPoints; return $this; }
    public function addPoints(int $points): static { $this->totalPoints += $points; return $this; }
    public function subtractPoints(int $points): static { $this->totalPoints = max(0, $this->totalPoints - $points); return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getSquad(): Squad { return $this->squad; }
    public function setSquad(Squad $squad): static { $this->squad = $squad; return $this; }

    public function getArrivedAt(): ?\DateTimeImmutable { return $this->arrivedAt; }
    public function setArrivedAt(?\DateTimeImmutable $arrivedAt): static { $this->arrivedAt = $arrivedAt; return $this; }

    public function getPlanet(): ?Planet { return $this->planet; }
    public function setPlanet(?Planet $planet): static { $this->planet = $planet; return $this; }

    /** @return Collection<int, Activity> */
    public function getActivities(): Collection { return $this->activities; }

    /** @return Collection<int, AstronautTrophy> */
    public function getTrophies(): Collection { return $this->trophies; }

    /** @return Collection<int, EventParticipant> */
    public function getEventParticipations(): Collection { return $this->eventParticipations; }

    // === UserInterface ===

    public function getUserIdentifier(): string { return $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function eraseCredentials(): void {}

    public function __toString(): string { return $this->getFullName(); }
}
