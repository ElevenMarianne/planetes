<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PlanetTrophy;
use App\Entity\Season;
use App\Repository\PlanetSeasonPointsRepository;
use App\Repository\SeasonRepository;
use App\Repository\TrophyRepository;
use Doctrine\ORM\EntityManagerInterface;

class SeasonService
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TrophyRepository $trophyRepository,
        private readonly PlanetSeasonPointsRepository $planetSeasonPointsRepository,
    ) {}

    public function getActiveSeason(): ?Season
    {
        return $this->seasonRepository->findActive();
    }

    public function activateSeason(Season $season): void
    {
        $this->entityManager->wrapInTransaction(function () use ($season) {
            // Désactiver toutes les saisons actives
            foreach ($this->seasonRepository->findBy(['isActive' => true]) as $active) {
                $active->setIsActive(false);
            }
            $season->setIsActive(true);
        });
    }

    public function closeSeason(Season $season): void
    {
        $today    = new \DateTimeImmutable('today');
        $tomorrow = $today->modify('+1 day');

        // Clôturer la saison courante
        $season->setIsActive(false);
        $season->setEndDate($today);

        // Trophée planète de la saison
        $trophy = $this->trophyRepository->findBySlug('best_planet_season');
        $winner = $this->planetSeasonPointsRepository->findTopPlanet($season);

        if ($trophy !== null && $winner !== null && $winner->getTotalPoints() > 0) {
            $planetTrophy = new PlanetTrophy();
            $planetTrophy->setPlanet($winner->getPlanet());
            $planetTrophy->setTrophy($trophy);
            $planetTrophy->setSeason($season);
            $this->entityManager->persist($planetTrophy);
        }

        // Créer et activer la saison suivante
        $nextEnd  = $tomorrow->modify('+1 year');
        $nextName = 'Saison ' . $tomorrow->format('Y') . '-' . $nextEnd->format('Y');

        $next = new Season();
        $next->setName($nextName);
        $next->setStartDate($tomorrow);
        $next->setEndDate($nextEnd);
        $next->setIsActive(true);
        $this->entityManager->persist($next);

        $this->entityManager->flush();
    }
}
