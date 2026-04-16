<?php

declare(strict_types=1);

namespace App\Message;

final readonly class SlackAnniversaryNotification
{
    public function __construct(
        public string $astronautName,
        public int $years,
        public int $points,
        public ?string $planetName = null,
    ) {}
}
