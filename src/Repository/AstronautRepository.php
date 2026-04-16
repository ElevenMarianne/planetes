<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Astronaut;
use App\Entity\Planet;
use App\Enum\PlanetType;
use App\Enum\Squad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Astronaut>
 */
class AstronautRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Astronaut::class);
    }

    public function findByEmailOrGoogleId(string $email, ?string $googleId = null): ?Astronaut
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.email = :email')
            ->setParameter('email', $email)
            ->andWhere('a.isActive = true');

        return $qb->getQuery()->getOneOrNullResult();
    }

    /** @return Astronaut[] */
    public function findActiveByPlanet(Planet $planet): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.planet = :planet')
            ->andWhere('a.isActive = true')
            ->setParameter('planet', $planet)
            ->orderBy('a.lastName', 'ASC')
            ->addOrderBy('a.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Astronaut[] */
    public function searchActive(string $query): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.planet', 'p')
            ->addSelect('p')
            ->andWhere('a.isActive = true')
            ->andWhere('LOWER(a.firstName) LIKE :q OR LOWER(a.lastName) LIKE :q OR LOWER(a.email) LIKE :q')
            ->setParameter('q', '%' . strtolower($query) . '%')
            ->orderBy('a.lastName', 'ASC')
            ->addOrderBy('a.firstName', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    /** @return Astronaut[] */
    public function findActiveFilteredByPlanet(?Planet $planet): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.planet', 'p')
            ->addSelect('p')
            ->andWhere('a.isActive = true');

        if ($planet !== null) {
            $qb->andWhere('a.planet = :planet')->setParameter('planet', $planet);
        } else {
            $qb->andWhere('p.type IN (:types) OR a.planet IS NULL')
               ->setParameter('types', [PlanetType::MAIN, PlanetType::NEWCOMER]);
        }

        return $qb->orderBy('a.lastName', 'ASC')
            ->addOrderBy('a.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Astronaut[] */
    public function findActiveFiltered(?Planet $planet, ?Squad $squad, string $search = ''): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.planet', 'p')
            ->addSelect('p')
            ->andWhere('a.isActive = true');

        if ($search !== '') {
            $qb->andWhere('LOWER(a.firstName) LIKE :q OR LOWER(a.lastName) LIKE :q OR LOWER(a.email) LIKE :q')
               ->setParameter('q', '%' . strtolower($search) . '%');
        }

        if ($planet !== null) {
            $qb->andWhere('a.planet = :planet')->setParameter('planet', $planet);
        } elseif ($search === '') {
            $qb->andWhere('p.type IN (:types) OR a.planet IS NULL')
               ->setParameter('types', [PlanetType::MAIN, PlanetType::NEWCOMER]);
        }

        if ($squad !== null) {
            $qb->andWhere('a.squad = :squad')->setParameter('squad', $squad->value);
        }

        return $qb->orderBy('a.lastName', 'ASC')
            ->addOrderBy('a.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Astronaut[] */
    public function findActiveByPlanetType(PlanetType $type): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.planet', 'p')
            ->addSelect('p')
            ->andWhere('a.isActive = true')
            ->andWhere('p.type = :type')
            ->setParameter('type', $type->value)
            ->orderBy('a.lastName', 'ASC')
            ->addOrderBy('a.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les astronautes actifs dont c'est aujourd'hui l'anniversaire d'arrivée
     * (même jour et même mois), avec au moins 1 an d'ancienneté.
     *
     * @return Astronaut[]
     */
    public function findActiveWithAnniversaryToday(): array
    {
        $today = new \DateTimeImmutable();
        $month = (int) $today->format('n');
        $day   = (int) $today->format('j');

        $astronauts = $this->createQueryBuilder('a')
            ->leftJoin('a.planet', 'p')
            ->addSelect('p')
            ->where('a.isActive = true')
            ->andWhere('a.arrivedAt IS NOT NULL')
            ->getQuery()
            ->getResult();

        return array_values(array_filter(
            $astronauts,
            static function (Astronaut $a) use ($month, $day, $today): bool {
                $arrived = $a->getArrivedAt();
                return (int) $arrived->format('n') === $month
                    && (int) $arrived->format('j') === $day
                    && $arrived->diff($today)->y >= 1;
            }
        ));
    }

    /** @return Astronaut[] */
    public function findAllActiveOrdered(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.planet', 'p')
            ->addSelect('p')
            ->andWhere('a.isActive = true')
            ->orderBy('a.lastName', 'ASC')
            ->addOrderBy('a.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
