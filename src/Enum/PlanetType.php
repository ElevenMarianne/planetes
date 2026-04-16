<?php

declare(strict_types=1);

namespace App\Enum;

enum PlanetType: string
{
    case MAIN = 'main';
    case NEWCOMER = 'newcomer';
    case REFEREE = 'referee';

    public function label(): string
    {
        return match($this) {
            self::MAIN => 'Planète principale',
            self::NEWCOMER => 'Planète des nouveaux arrivants',
            self::REFEREE => 'Planète des arbitres',
        };
    }

    public function isCompetitor(): bool
    {
        return $this === self::MAIN;
    }
}
