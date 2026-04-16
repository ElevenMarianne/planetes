<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Repository\AstronautRepository;
use App\Repository\PlanetRepository;
use App\Repository\SeasonRepository;
use App\Service\SeasonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/back', name: 'back_')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly AstronautRepository $astronautRepository,
        private readonly PlanetRepository $planetRepository,
        private readonly SeasonService $seasonService,
        private readonly SeasonRepository $seasonRepository,
    ) {}

    #[Route('', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render('back/dashboard.html.twig', [
            'activeSeason' => $this->seasonService->getActiveSeason(),
            'astronautCount' => count($this->astronautRepository->findAllActiveOrdered()),
            'planetCount' => count($this->planetRepository->findCompetitors()),
            'seasons' => $this->seasonRepository->findAllOrdered(),
        ]);
    }
}
