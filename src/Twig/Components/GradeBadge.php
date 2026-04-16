<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Grade;
use App\Service\GradeService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class GradeBadge
{
    public int $points = 0;
    public ?Grade $grade = null;

    public function __construct(
        private readonly GradeService $gradeService,
    ) {}

    public function mount(int $points): void
    {
        $this->points = $points;
        $this->grade = $this->gradeService->getGradeForPoints($points);
    }
}
