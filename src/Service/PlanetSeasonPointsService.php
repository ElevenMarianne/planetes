<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Planet;
use App\Entity\PlanetSeasonPoints;
use App\Entity\Season;
use App\Repository\ActivityRepository;
use App\Repository\PlanetSeasonPointsRepository;
use Doctrine\ORM\EntityManagerInterface;

class PlanetSeasonPointsService
{
    public function __construct(
        private readonly PlanetSeasonPointsRepository $repository,
        private readonly ActivityRepository $activityRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function getOrCreate(Planet $planet, Season $season): PlanetSeasonPoints
    {
        $psp = $this->repository->findByPlanetAndSeason($planet, $season);

        if ($psp === null) {
            $psp = new PlanetSeasonPoints();
            $psp->setPlanet($planet);
            $psp->setSeason($season);
            $this->entityManager->persist($psp);
        }

        return $psp;
    }

    public function addPoints(Planet $planet, Season $season, int $points): void
    {
        $psp = $this->getOrCreate($planet, $season);
        $psp->addPoints($points);
        $this->entityManager->flush();
    }

    public function subtractPoints(Planet $planet, Season $season, int $points): void
    {
        $psp = $this->getOrCreate($planet, $season);
        $psp->subtractPoints($points);
        $this->entityManager->flush();
    }

    /**
     * Recalcul complet depuis les activités — à utiliser comme filet de sécurité via CLI.
     */
    public function recalculate(Season $season): void
    {
        $activities = $this->activityRepository->findBySeasonOrdered($season);
        $totals = [];

        foreach ($activities as $activity) {
            if ($activity->getPlanet() === null) {
                continue;
            }
            $planetId = $activity->getPlanet()->getId();
            $totals[$planetId] = ($totals[$planetId] ?? 0) + $activity->getPoints();
        }

        foreach ($totals as $planetId => $total) {
            $planet = $this->entityManager->getReference(Planet::class, $planetId);
            $psp = $this->getOrCreate($planet, $season);
            $psp->setTotalPoints($total);
        }

        $this->entityManager->flush();
    }
}
