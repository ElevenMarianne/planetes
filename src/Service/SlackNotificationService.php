<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Astronaut;
use App\Entity\Planet;
use App\Entity\Trophy;
use App\Message\SlackPointsNotification;
use App\Message\SlackTrophyNotification;
use Symfony\Component\Messenger\MessageBusInterface;

class SlackNotificationService
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {}

    public function notifyPointsAwarded(Astronaut $astronaut, int $points, Activity $activity): void
    {
        $initial      = mb_strtoupper(mb_substr($astronaut->getLastName(), 0, 1));
        $shortName    = $astronaut->getFirstName() . ' ' . $initial . '.';

        $this->bus->dispatch(new SlackPointsNotification(
            astronautName: $shortName,
            astronautEmail: $astronaut->getEmail(),
            points: $points,
            activityName: $activity->getType()->getName(),
            seasonName: $activity->getSeason()->getName(),
            planetName: $astronaut->getPlanet()?->getName(),
        ));
    }

    public function notifyTrophyAwarded(Astronaut|Planet $recipient, Trophy $trophy, ?string $seasonName = null): void
    {
        $this->bus->dispatch(new SlackTrophyNotification(
            recipientName: $recipient instanceof Planet ? $recipient->getName() : $recipient->getFullName(),
            trophyName: $trophy->getName(),
            isPlanet: $recipient instanceof Planet,
            seasonName: $seasonName,
        ));
    }
}
