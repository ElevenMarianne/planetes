<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\Astronaut;
use App\Entity\Grade;
use App\Service\GradeService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GradeExtension extends AbstractExtension
{
    public function __construct(
        private readonly GradeService $gradeService,
    ) {}

    public function getFilters(): array
    {
        return [
            new TwigFilter('grade', [$this, 'getGrade']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('grade_for_points', [$this, 'getGradeForPoints']),
        ];
    }

    public function getGrade(Astronaut $astronaut): ?Grade
    {
        return $this->gradeService->getGradeForAstronaut($astronaut);
    }

    public function getGradeForPoints(int $points): ?Grade
    {
        return $this->gradeService->getGradeForPoints($points);
    }
}
