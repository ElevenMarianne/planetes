<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Season;
use App\Repository\PlanetSeasonPointsRepository;
use App\Service\SeasonService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/Leaderboard.html.twig')]
class Leaderboard
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?Season $season = null;

    public function __construct(
        private readonly PlanetSeasonPointsRepository $pspRepository,
        private readonly SeasonService $seasonService,
    ) {}

    public function mount(): void
    {
        if ($this->season === null) {
            $this->season = $this->seasonService->getActiveSeason();
        }
    }

    public function getLeaderboardData(): array
    {
        if ($this->season === null) {
            return [];
        }
        return $this->pspRepository->getLeaderboard($this->season);
    }
}
