<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\Astronaut;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function countByAstronaut(Astronaut $astronaut): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.astronauts', 'ast')
            ->andWhere('ast = :astronaut')
            ->setParameter('astronaut', $astronaut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByAstronautAndSeason(Astronaut $astronaut, Season $season): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.astronauts', 'ast')
            ->andWhere('ast = :astronaut')
            ->andWhere('a.season = :season')
            ->setParameter('astronaut', $astronaut)
            ->setParameter('season', $season)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Activity[] */
    public function findByAstronautOrdered(Astronaut $astronaut): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.astronauts', 'ast')
            ->leftJoin('a.type', 't')
            ->addSelect('t')
            ->andWhere('ast = :astronaut')
            ->setParameter('astronaut', $astronaut)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Activity[] */
    public function findByAstronautPaginated(Astronaut $astronaut, int $page, int $perPage = 10): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.astronauts', 'ast')
            ->leftJoin('a.type', 't')->addSelect('t')
            ->leftJoin('a.season', 's')->addSelect('s')
            ->andWhere('ast = :astronaut')
            ->setParameter('astronaut', $astronaut)
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    /** @return Activity[] */
    public function findBySeasonOrdered(Season $season): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.type', 't')
            ->addSelect('t')
            ->andWhere('a.season = :season')
            ->setParameter('season', $season)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Activity[] */
    public function findBySeasonPaginated(Season $season, int $page, int $perPage): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.type', 't')->addSelect('t')
            ->leftJoin('a.planet', 'p')->addSelect('p')
            ->leftJoin('a.astronauts', 'ast')->addSelect('ast')
            ->andWhere('a.season = :season')
            ->setParameter('season', $season)
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countBySeason(Season $season): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.season = :season')
            ->setParameter('season', $season)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Activity[] */
    public function findByPlanetAndSeasonPaginated(
        \App\Entity\Planet $planet,
        Season $season,
        int $page,
        int $perPage,
    ): array {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.type', 't')->addSelect('t')
            ->leftJoin('a.astronauts', 'ast')->addSelect('ast')
            ->andWhere('a.planet = :planet')
            ->andWhere('a.season = :season')
            ->setParameter('planet', $planet)
            ->setParameter('season', $season)
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countByPlanetAndSeason(\App\Entity\Planet $planet, Season $season): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.planet = :planet')
            ->andWhere('a.season = :season')
            ->setParameter('planet', $planet)
            ->setParameter('season', $season)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
