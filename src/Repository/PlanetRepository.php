<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Planet;
use App\Enum\PlanetType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Planet>
 */
class PlanetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planet::class);
    }

    /** @return Planet[] */
    public function findCompetitors(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.type = :type')
            ->setParameter('type', PlanetType::MAIN)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Planet[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.type', 'ASC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Planètes des types donnés ayant au moins un astronaute actif.
     *
     * @return Planet[]
     */
    public function findWithActiveAstronauts(PlanetType ...$types): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.astronauts', 'a')
            ->andWhere('a.isActive = true')
            ->andWhere('p.type IN (:types)')
            ->setParameter('types', $types)
            ->distinct()
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
