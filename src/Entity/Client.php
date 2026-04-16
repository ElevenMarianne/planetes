<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $name = '';

    #[ORM\Column(length: 170, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private string $slug = '';

    #[ORM\OneToMany(targetEntity: Astronaut::class, mappedBy: 'client')]
    private Collection $astronauts;

    public function __construct()
    {
        $this->astronauts = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    /** @return Collection<int, Astronaut> */
    public function getAstronauts(): Collection { return $this->astronauts; }

    public function __toString(): string { return $this->name; }
}
