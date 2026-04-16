<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Astronaut;
use App\Entity\AstronautTrophy;
use App\Entity\Planet;
use App\Entity\PlanetTrophy;
use App\Entity\Trophy;
use App\Repository\AstronautRepository;
use App\Repository\PlanetRepository;
use App\Repository\SeasonRepository;
use App\Repository\TrophyRepository;
use App\Service\FileUploadService;
use App\Service\SeasonService;
use App\Service\SlackNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/back/trophies', name: 'back_trophies_')]
class TrophyController extends AbstractController
{
    public function __construct(
        private readonly TrophyRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly FileUploadService $fileUploadService,
        private readonly SeasonService $seasonService,
        private readonly AstronautRepository $astronautRepository,
        private readonly PlanetRepository $planetRepository,
        private readonly SlackNotificationService $slackService,
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('back/trophy/index.html.twig', [
            'trophies' => $this->repository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $trophy = new Trophy();
        return $this->handleForm($request, $trophy, false);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Trophy $trophy): Response
    {
        return $this->handleForm($request, $trophy, true);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Trophy $trophy): Response
    {
        if ($this->isCsrfTokenValid('delete_trophy_' . $trophy->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($trophy);
            $this->entityManager->flush();
            $this->addFlash('success', "Trophée \"{$trophy->getName()}\" supprimé.");
        }
        return $this->redirectToRoute('back_trophies_index');
    }

    #[Route('/award', name: 'award', methods: ['GET', 'POST'])]
    public function award(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $trophyId = $request->request->getInt('trophy');
            $target = $request->request->getString('target'); // 'astronaut' | 'planet'
            $targetId = $request->request->getInt('targetId');

            $trophy = $this->repository->find($trophyId);
            $season = $this->seasonService->getActiveSeason();

            if ($trophy === null) {
                $this->addFlash('error', 'Trophée introuvable.');
                return $this->redirectToRoute('back_trophies_award');
            }

            if ($target === 'astronaut') {
                $astronaut = $this->astronautRepository->find($targetId);
                if ($astronaut instanceof Astronaut) {
                    $at = new AstronautTrophy();
                    $at->setTrophy($trophy)->setAstronaut($astronaut)->setSeason($season);
                    $this->entityManager->persist($at);
                    $this->entityManager->flush();
                    $this->slackService->notifyTrophyAwarded($astronaut, $trophy);
                    $this->addFlash('success', "Trophée attribué à {$astronaut->getFullName()}.");
                }
            } elseif ($target === 'planet') {
                $planet = $this->planetRepository->find($targetId);
                if ($planet instanceof Planet) {
                    $pt = new PlanetTrophy();
                    $pt->setTrophy($trophy)->setPlanet($planet)->setSeason($season);
                    $this->entityManager->persist($pt);
                    $this->entityManager->flush();
                    $this->addFlash('success', "Trophée attribué à la planète {$planet->getName()}.");
                }
            }

            return $this->redirectToRoute('back_trophies_index');
        }

        return $this->render('back/trophy/award.html.twig', [
            'trophies' => $this->repository->findBy([], ['name' => 'ASC']),
            'astronauts' => $this->astronautRepository->findAllActiveOrdered(),
            'planets' => $this->planetRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    private function handleForm(Request $request, Trophy $trophy, bool $isEdit): Response
    {
        if ($request->isMethod('POST')) {
            $trophy->setName($request->request->getString('name'));
            $trophy->setDescription($request->request->getString('description') ?: null);

            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image');
            if ($imageFile !== null) {
                $path = $this->fileUploadService->upload($imageFile, 'trophies');
                $trophy->setImage($path);
            }

            $errors = $this->validator->validate($trophy);
            if (count($errors) === 0) {
                $this->entityManager->persist($trophy);
                $this->entityManager->flush();
                $this->addFlash('success', $isEdit ? "Trophée mis à jour." : "Trophée créé.");
                return $this->redirectToRoute('back_trophies_index');
            }

            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('back/trophy/form.html.twig', [
            'trophy' => $trophy,
            'isEdit' => $isEdit,
        ]);
    }
}
