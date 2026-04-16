<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Grade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Grade>
 */
class GradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grade::class);
    }

    public function findForPoints(int $points): ?Grade
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.minPoints <= :points')
            ->setParameter('points', $points)
            ->orderBy('g.minPoints', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return Grade[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.minPoints', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
