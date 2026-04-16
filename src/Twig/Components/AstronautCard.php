<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Astronaut;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class AstronautCard
{
    public Astronaut $astronaut;
}
