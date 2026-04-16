<?php

declare(strict_types=1);

namespace App\Enum;

enum Squad: string
{
    case PARIS      = 'paris';
    case NANTES     = 'nantes';
    case PROVINCIAL = 'provincial';

    public function label(): string
    {
        return match($this) {
            self::PARIS      => 'Paris',
            self::NANTES     => 'Nantes',
            self::PROVINCIAL => 'Provinciaux',
        };
    }
}
