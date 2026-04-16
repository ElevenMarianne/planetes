<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Activity;
use App\Entity\ActivityType;
use App\Entity\Astronaut;
use App\Repository\ActivityTypeRepository;
use App\Repository\AstronautRepository;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/ActivityFormComponent.html.twig')]
class ActivityFormComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?int $selectedTypeId = null;

    #[LiveProp(writable: true)]
    public array $selectedAstronautIds = [];

    #[LiveProp(writable: true)]
    public ?string $note = null;

    private ?ActivityType $resolvedType = null;

    public function __construct(
        private readonly ActivityTypeRepository $activityTypeRepository,
        private readonly AstronautRepository $astronautRepository,
        private readonly SeasonService $seasonService,
    ) {}

    public function getActivityTypes(): array
    {
        return $this->activityTypeRepository->findBy(['isActive' => true], ['name' => 'ASC']);
    }

    public function getAstronauts(): array
    {
        return $this->astronautRepository->findAllActiveOrdered();
    }

    public function getSelectedType(): ?ActivityType
    {
        if ($this->selectedTypeId === null) {
            return null;
        }

        if ($this->resolvedType === null || $this->resolvedType->getId() !== $this->selectedTypeId) {
            $this->resolvedType = $this->activityTypeRepository->find($this->selectedTypeId);
        }

        return $this->resolvedType;
    }

    public function allowsMultiple(): bool
    {
        $type = $this->getSelectedType();
        return $type !== null && $type->isAllowsMultipleParticipants();
    }

    public function getBasePoints(): int
    {
        $type = $this->getSelectedType();
        return $type !== null ? $type->getBasePoints() : 0;
    }

    public function getSelectedAstronauts(): array
    {
        if (empty($this->selectedAstronautIds)) {
            return [];
        }
        return $this->astronautRepository->findBy(['id' => $this->selectedAstronautIds]);
    }

    public function getEstimatedPoints(): int
    {
        $base = $this->getBasePoints();
        $count = count($this->selectedAstronautIds);

        if ($base === 0 || $count === 0) {
            return 0;
        }

        // Chaque astronaute reçoit le montant complet
        return $base * $count;
    }
}
