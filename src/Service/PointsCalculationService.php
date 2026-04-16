<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Astronaut;
use App\Entity\Season;
use App\Enum\PointsTarget;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service central pour le calcul et l'attribution des points.
 *
 * Règles métier :
 * - Les points de base viennent de ActivityType::basePoints
 * - Première contribution ever (tous types confondus) → points × 2
 * - Première contribution de la saison → +25 pts bonus (appliqué après le x2 si applicable)
 * - Pour les activités en duo : chaque astronaute reçoit le montant complet
 * - Les bonus first-contribution sont évalués individuellement pour chaque astronaute
 */
class PointsCalculationService
{
    public function __construct(
        private readonly ActivityRepository $activityRepository,
        private readonly PlanetSeasonPointsService $planetSeasonPointsService,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Attribue les points pour une activité nouvellement créée.
     * L'activité doit déjà avoir ses astronautes, type, saison et planète définis.
     *
     * @return array<string, mixed> Résumé des points attribués par astronaute
     */
    public function attributePoints(Activity $activity): array
    {
        $type        = $activity->getType();
        $target      = $type->getPointsTarget();
        $basePoints  = $type->getBasePoints();
        $season      = $activity->getSeason();
        $planet      = $activity->getPlanet();

        // Challenge interplanétaire : points fixes pour la planète, aucun bonus, aucun point astro
        if ($target === PointsTarget::PLANET_ONLY) {
            $activity->setPoints($basePoints);
            if ($planet !== null) {
                $this->planetSeasonPointsService->addPoints($planet, $season, $basePoints);
            }
            $this->entityManager->flush();
            return [['points' => $basePoints, 'target' => 'planet']];
        }

        // Calcul par astronaute (avec bonus first-ever / first-season)
        $results = [];
        foreach ($activity->getAstronauts() as $astronaut) {
            $finalPoints = $this->calculateForAstronaut($astronaut, $season, $basePoints);
            $astronaut->addPoints($finalPoints);
            $results[] = ['astronaut' => $astronaut, 'points' => $finalPoints, 'basePoints' => $basePoints];
        }

        $totalPoints = array_sum(array_column($results, 'points'));
        $activity->setPoints($totalPoints);

        // BOTH : les points de chaque astronaute vont à sa propre planète
        if ($target === PointsTarget::BOTH) {
            foreach ($results as $result) {
                $astronautPlanet = $result['astronaut']->getPlanet();
                if ($astronautPlanet !== null) {
                    $this->planetSeasonPointsService->addPoints($astronautPlanet, $season, $result['points']);
                }
            }
        }
        // ASTRONAUT_ONLY (ancienneté) : rien pour la planète

        $this->entityManager->flush();
        return $results;
    }

    /**
     * Inverse les points d'une activité supprimée.
     * Doit être appelé AVANT la suppression de l'entité.
     */
    public function reversePoints(Activity $activity): void
    {
        $target = $activity->getType()->getPointsTarget();
        $season = $activity->getSeason();

        if ($target === PointsTarget::PLANET_ONLY) {
            $planet = $activity->getPlanet();
            if ($planet !== null) {
                $this->planetSeasonPointsService->subtractPoints($planet, $season, $activity->getPoints());
            }
            $this->entityManager->flush();
            return;
        }

        $astronautCount = max(1, $activity->getAstronauts()->count());
        foreach ($activity->getAstronauts() as $astronaut) {
            $share = (int) round($activity->getPoints() / $astronautCount);
            $astronaut->subtractPoints($share);

            // BOTH : retirer les points à la planète de chaque astronaute
            if ($target === PointsTarget::BOTH) {
                $astronautPlanet = $astronaut->getPlanet();
                if ($astronautPlanet !== null) {
                    $this->planetSeasonPointsService->subtractPoints($astronautPlanet, $season, $share);
                }
            }
        }
        // ASTRONAUT_ONLY : rien à retirer à la planète

        $this->entityManager->flush();
    }

    private function calculateForAstronaut(Astronaut $astronaut, Season $season, int $basePoints): int
    {
        $totalActivityCount = $this->activityRepository->countByAstronaut($astronaut);
        $seasonActivityCount = $this->activityRepository->countByAstronautAndSeason($astronaut, $season);

        $points = $basePoints;

        // Règle : première contribution ever → × 2
        if ($totalActivityCount === 0) {
            $points = $points * 2;
        }

        // Règle : première contribution de la saison → +25 pts bonus
        if ($seasonActivityCount === 0) {
            $points += 25;
        }

        return $points;
    }
}
