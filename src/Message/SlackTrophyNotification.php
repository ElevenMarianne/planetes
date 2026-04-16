<?php

declare(strict_types=1);

namespace App\Message;

final readonly class SlackTrophyNotification
{
    public function __construct(
        public string $recipientName,
        public string $trophyName,
        public bool $isPlanet = false,
        public ?string $seasonName = null,
    ) {}
}
