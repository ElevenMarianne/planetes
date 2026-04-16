<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Astronaut;
use App\Entity\Grade;
use App\Repository\GradeRepository;

class GradeService
{
    public function __construct(
        private readonly GradeRepository $gradeRepository,
    ) {}

    public function getGradeForPoints(int $points): ?Grade
    {
        return $this->gradeRepository->findForPoints($points);
    }

    public function getGradeForAstronaut(Astronaut $astronaut): ?Grade
    {
        return $this->getGradeForPoints($astronaut->getTotalPoints());
    }
}
