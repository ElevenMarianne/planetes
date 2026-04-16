<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Trophy;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class TrophyBadge
{
    public Trophy $trophy;
    public ?string $awardedAt = null;
}
