<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActivityType;
use App\Enum\ActivityUniqueName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActivityType>
 */
class ActivityTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityType::class);
    }

    public function findByUniqueName(ActivityUniqueName $uniqueName): ?ActivityType
    {
        return $this->findOneBy(['uniqueName' => $uniqueName]);
    }

    /** @return ActivityType[] */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('at')
            ->andWhere('at.isActive = true')
            ->orderBy('at.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
