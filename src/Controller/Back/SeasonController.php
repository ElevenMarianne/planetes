<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Season;
use App\Repository\SeasonRepository;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/back/seasons', name: 'back_seasons_')]
class SeasonController extends AbstractController
{
    public function __construct(
        private readonly SeasonRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SeasonService $seasonService,
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('back/season/index.html.twig', [
            'seasons' => $this->repository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $season = new Season();
        // Form simple inline
        if ($request->isMethod('POST')) {
            $season->setName($request->request->getString('name'));
            $season->setStartDate(new \DateTimeImmutable($request->request->getString('startDate')));
            $season->setEndDate(new \DateTimeImmutable($request->request->getString('endDate')));
            $this->entityManager->persist($season);
            $this->entityManager->flush();
            $this->addFlash('success', 'Saison créée.');
            return $this->redirectToRoute('back_seasons_index');
        }

        return $this->render('back/season/form.html.twig', ['season' => $season]);
    }

    #[Route('/{id}/activate', name: 'activate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function activate(Season $season): Response
    {
        $this->seasonService->activateSeason($season);
        $this->addFlash('success', "Saison \"{$season->getName()}\" activée.");
        return $this->redirectToRoute('back_seasons_index');
    }

    #[Route('/{id}/close', name: 'close', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function close(Season $season): Response
    {
        if (!$season->isActive()) {
            $this->addFlash('error', 'Cette saison est déjà terminée.');
            return $this->redirectToRoute('back_seasons_index');
        }

        $this->seasonService->closeSeason($season);
        $this->addFlash('success', "Saison \"{$season->getName()}\" clôturée (date de fin fixée à aujourd'hui). La saison suivante a été créée et activée.");
        return $this->redirectToRoute('back_seasons_index');
    }
}
