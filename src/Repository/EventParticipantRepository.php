<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Astronaut;
use App\Entity\Event;
use App\Entity\EventParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventParticipant>
 */
class EventParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventParticipant::class);
    }

    public function findByEventAndAstronaut(Event $event, Astronaut $astronaut): ?EventParticipant
    {
        return $this->createQueryBuilder('ep')
            ->andWhere('ep.event = :event')
            ->andWhere('ep.astronaut = :astronaut')
            ->setParameter('event', $event)
            ->setParameter('astronaut', $astronaut)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
