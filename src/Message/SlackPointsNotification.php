<?php

declare(strict_types=1);

namespace App\Message;

final readonly class SlackPointsNotification
{
    public function __construct(
        public string $astronautName,
        public string $astronautEmail,
        public int $points,
        public string $activityName,
        public ?string $seasonName = null,
        public ?string $planetName = null,
    ) {}
}
