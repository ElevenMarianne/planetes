<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\Astronaut;
use App\Enum\PlanetType;
use App\Enum\Squad;
use App\Repository\AstronautRepository;
use App\Repository\ActivityRepository;
use App\Repository\PlanetRepository;
use App\Service\GradeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/astronauts', name: 'front_astronauts')]
class AstronautController extends AbstractController
{
    public function __construct(
        private readonly AstronautRepository $astronautRepository,
        private readonly ActivityRepository $activityRepository,
        private readonly GradeService $gradeService,
        private readonly PlanetRepository $planetRepository,
    ) {}

    #[Route('', name: '')]
    public function index(): Response
    {
        $astronauts = $this->astronautRepository->findAllActiveOrdered();

        $planets = $this->planetRepository->findWithActiveAstronauts(
            PlanetType::MAIN,
            PlanetType::NEWCOMER,
        );

        return $this->render('front/astronaut/index.html.twig', [
            'astronauts' => $astronauts,
            'planets'    => $planets,
            'squads'     => Squad::cases(),
        ]);
    }

    #[Route('/{id}', name: '_show', requirements: ['id' => '\d+'])]
    public function show(Astronaut $astronaut, Request $request): Response
    {
        $perPage     = 10;
        $totalCount  = $this->activityRepository->countByAstronaut($astronaut);
        $totalPages  = max(1, (int) ceil($totalCount / $perPage));
        $currentPage = max(1, min($totalPages, (int) $request->query->get('page', 1)));

        $activities = $this->activityRepository->findByAstronautPaginated($astronaut, $currentPage, $perPage);
        $grade      = $this->gradeService->getGradeForAstronaut($astronaut);

        return $this->render('front/astronaut/show.html.twig', [
            'astronaut'   => $astronaut,
            'activities'  => $activities,
            'grade'       => $grade,
            'currentPage' => $currentPage,
            'totalPages'  => $totalPages,
            'totalCount'  => $totalCount,
        ]);
    }
}
