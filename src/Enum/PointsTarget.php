<?php

declare(strict_types=1);

namespace App\Enum;

enum PointsTarget: string
{
    case BOTH           = 'both';
    case PLANET_ONLY    = 'planet';
    case ASTRONAUT_ONLY = 'astronaut';

    public function label(): string
    {
        return match($this) {
            self::BOTH           => 'Astronaute + Planète',
            self::PLANET_ONLY    => 'Planète uniquement',
            self::ASTRONAUT_ONLY => 'Astronaute uniquement',
        };
    }
}
