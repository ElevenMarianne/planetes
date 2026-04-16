<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Planet;
use App\Entity\PlanetSeasonPoints;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PlanetCard
{
    public Planet $planet;
    public ?PlanetSeasonPoints $seasonPoints = null;
}
