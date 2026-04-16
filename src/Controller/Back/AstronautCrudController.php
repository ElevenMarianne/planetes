<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Astronaut;
use App\Form\Back\AstronautType;
use App\Repository\AstronautRepository;
use App\Repository\PlanetRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/back/astronauts', name: 'back_astronauts_')]
class AstronautCrudController extends AbstractController
{
    public function __construct(
        private readonly AstronautRepository $repository,
        private readonly PlanetRepository $planetRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploadService $fileUploadService,
    ) {}

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query->getString('q', ''));
        $astronauts = $this->repository->searchActive($q);

        return $this->json(array_map(static fn (Astronaut $a) => [
            'id'          => $a->getId(),
            'fullName'    => $a->getFullName(),
            'email'       => $a->getEmail(),
            'photo'       => $a->getPhoto(),
            'planetId'    => $a->getPlanet()?->getId(),
            'planetName'  => $a->getPlanet()?->getName(),
            'planetColor' => $a->getPlanet()?->getColor(),
            'planetPhoto' => $a->getPlanet()?->getPhoto(),
        ], $astronauts));
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('back/astronaut/index.html.twig', [
            'astronauts' => $this->repository->findAllActiveOrdered(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $astronaut = new Astronaut();
        $asteroid = $this->planetRepository->findOneBy(['slug' => 'asteroide']);
        if ($asteroid !== null) {
            $astronaut->setPlanet($asteroid);
        }
        $form = $this->createForm(AstronautType::class, $astronaut);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $astronaut->setPhoto($this->fileUploadService->upload($photoFile, 'astronauts'));
            }
            $this->entityManager->persist($astronaut);
            $this->entityManager->flush();
            $this->addFlash('success', 'Astronaute créé.');
            return $this->redirectToRoute('back_astronauts_index');
        }

        return $this->render('back/astronaut/form.html.twig', ['form' => $form, 'astronaut' => $astronaut]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Astronaut $astronaut): Response
    {
        $form = $this->createForm(AstronautType::class, $astronaut);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                if ($astronaut->getPhoto()) {
                    $this->fileUploadService->remove($astronaut->getPhoto());
                }
                $astronaut->setPhoto($this->fileUploadService->upload($photoFile, 'astronauts'));
            }
            $this->entityManager->flush();
            $this->addFlash('success', 'Astronaute modifié.');
            return $this->redirectToRoute('back_astronauts_index');
        }

        return $this->render('back/astronaut/form.html.twig', ['form' => $form, 'astronaut' => $astronaut]);
    }

#[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Astronaut $astronaut): Response
    {
        if ($this->isCsrfTokenValid('delete_astronaut_' . $astronaut->getId(), $request->request->get('_token'))) {
            $astronaut->setIsActive(false); // Soft delete
            $this->entityManager->flush();
            $this->addFlash('success', 'Astronaute désactivé.');
        }
        return $this->redirectToRoute('back_astronauts_index');
    }
}
