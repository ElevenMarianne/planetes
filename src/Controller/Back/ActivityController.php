<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Activity;
use App\Form\Back\ActivityFormType;
use App\Repository\ActivityRepository;
use App\Repository\ActivityTypeRepository;
use App\Service\PointsCalculationService;
use App\Service\SeasonService;
use App\Service\SlackNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/back/activities', name: 'back_activities_')]
class ActivityController extends AbstractController
{
    public function __construct(
        private readonly ActivityRepository $repository,
        private readonly ActivityTypeRepository $activityTypeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly PointsCalculationService $pointsService,
        private readonly SlackNotificationService $slackService,
        private readonly SeasonService $seasonService,
    ) {}

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        $season  = $this->seasonService->getActiveSeason();
        $perPage = 20;

        $totalCount  = $season ? $this->repository->countBySeason($season) : 0;
        $totalPages  = max(1, (int) ceil($totalCount / $perPage));
        $currentPage = max(1, min($totalPages, (int) $request->query->get('page', 1)));

        $activities = $season ? $this->repository->findBySeasonPaginated($season, $currentPage, $perPage) : [];

        return $this->render('back/activity/index.html.twig', [
            'activities'  => $activities,
            'season'      => $season,
            'currentPage' => $currentPage,
            'totalPages'  => $totalPages,
            'totalCount'  => $totalCount,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $activity = new Activity();

        // Pré-remplir avec la saison active
        $activeSeason = $this->seasonService->getActiveSeason();
        if ($activeSeason) {
            $activity->setSeason($activeSeason);
        }

        // Map id → {allowsMultiple, planetOnly} pour le JS
        $activityTypesMap = [];
        foreach ($this->activityTypeRepository->findBy(['isActive' => true]) as $at) {
            $activityTypesMap[$at->getId()] = [
                'allowsMultiple' => $at->isAllowsMultipleParticipants(),
                'planetOnly'     => $at->isPlanetOnly(),
            ];
        }

        $form = $this->createForm(ActivityFormType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type  = $activity->getType();
            $count = $activity->getAstronauts()->count();

            if ($type !== null) {
                if ($type->isPlanetOnly()) {
                    if ($activity->getPlanet() === null) {
                        $this->addFlash('error', 'Cette activité attribue des points à une planète : sélectionnez une planète.');
                    } else {
                        $this->entityManager->persist($activity);
                        $this->pointsService->attributePoints($activity);
                        $this->addFlash('success', sprintf(
                            'Activité créée. Points attribués à la planète %s.',
                            $activity->getPlanet()->getName()
                        ));
                        return $this->redirectToRoute('back_activities_index');
                    }
                } elseif (!$type->isAllowsMultipleParticipants() && $count !== 1) {
                    $this->addFlash('error', 'Cette activité est individuelle : sélectionnez exactement 1 astronaute.');
                } elseif ($type->isAllowsMultipleParticipants() && $count !== 2) {
                    $this->addFlash('error', 'Cette activité est en duo : sélectionnez exactement 2 astronautes.');
                } else {
                    if ($activity->getPlanet() === null && !$activity->getAstronauts()->isEmpty()) {
                        $activity->setPlanet($activity->getAstronauts()->first()->getPlanet());
                    }

                    $this->entityManager->persist($activity);
                    $results = $this->pointsService->attributePoints($activity);

                    foreach ($results as $result) {
                        $this->slackService->notifyPointsAwarded($result['astronaut'], $result['points'], $activity);
                    }

                    $this->addFlash('success', sprintf(
                        'Activité créée. %d astronaute(s) ont reçu des points.',
                        count($results)
                    ));
                    return $this->redirectToRoute('back_activities_index');
                }
            }
        }

        return $this->render('back/activity/form.html.twig', [
            'form'             => $form,
            'activity'         => $activity,
            'activeSeason'     => $activeSeason,
            'activityTypesMap' => $activityTypesMap,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Activity $activity): Response
    {
        if ($this->isCsrfTokenValid('delete_activity_' . $activity->getId(), $request->request->get('_token'))) {
            $this->pointsService->reversePoints($activity);
            $this->entityManager->remove($activity);
            $this->entityManager->flush();
            $this->addFlash('success', 'Activité supprimée et points retirés.');
        }
        return $this->redirectToRoute('back_activities_index');
    }
}
