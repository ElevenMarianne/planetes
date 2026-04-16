<?php

declare(strict_types=1);

namespace App\Message;

final readonly class SlackAttendanceNotification
{
    /**
     * @param array<array{planet: string, count: int, names: string}> $byPlanet
     */
    public function __construct(
        public string $eventName,
        public array  $byPlanet,
    ) {}
}
