<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Planet;
use App\Entity\PlanetSeasonPoints;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlanetSeasonPoints>
 */
class PlanetSeasonPointsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanetSeasonPoints::class);
    }

    public function findByPlanetAndSeason(Planet $planet, Season $season): ?PlanetSeasonPoints
    {
        return $this->createQueryBuilder('psp')
            ->andWhere('psp.planet = :planet')
            ->andWhere('psp.season = :season')
            ->setParameter('planet', $planet)
            ->setParameter('season', $season)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findTopPlanet(Season $season): ?PlanetSeasonPoints
    {
        return $this->createQueryBuilder('psp')
            ->leftJoin('psp.planet', 'p')
            ->addSelect('p')
            ->andWhere('psp.season = :season')
            ->setParameter('season', $season)
            ->orderBy('psp.totalPoints', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne le classement des planètes pour une saison, du plus haut au plus bas score.
     * @return PlanetSeasonPoints[]
     */
    public function getLeaderboard(Season $season): array
    {
        return $this->createQueryBuilder('psp')
            ->leftJoin('psp.planet', 'p')
            ->addSelect('p')
            ->andWhere('psp.season = :season')
            ->setParameter('season', $season)
            ->orderBy('psp.totalPoints', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
