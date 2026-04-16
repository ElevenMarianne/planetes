<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Planet;
use App\Enum\PlanetType;
use App\Repository\PlanetRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/back/planets', name: 'back_planets_')]
class PlanetCrudController extends AbstractController
{
    public function __construct(
        private readonly PlanetRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly FileUploadService $fileUploadService,
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('back/planet/index.html.twig', [
            'planets' => $this->repository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $planet = new Planet();
        return $this->handleForm($request, $planet, false);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Planet $planet): Response
    {
        return $this->handleForm($request, $planet, true);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Planet $planet): Response
    {
        if ($this->isCsrfTokenValid('delete_planet_' . $planet->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($planet);
            $this->entityManager->flush();
            $this->addFlash('success', "Planète \"{$planet->getName()}\" supprimée.");
        }
        return $this->redirectToRoute('back_planets_index');
    }

    private function handleForm(Request $request, Planet $planet, bool $isEdit): Response
    {
        if ($request->isMethod('POST')) {
            $planet->setName($request->request->getString('name'));
            $planet->setMantra($request->request->getString('mantra') ?: null);
            $planet->setColor($request->request->getString('color', '#6366f1'));
            $planet->setType(PlanetType::from($request->request->getString('type', PlanetType::MAIN->value)));

            /** @var UploadedFile|null $photoFile */
            $photoFile = $request->files->get('photo');
            if ($photoFile !== null) {
                $path = $this->fileUploadService->upload($photoFile, 'planets');
                $planet->setPhoto($path);
            }

            /** @var UploadedFile|null $artworkFile */
            $artworkFile = $request->files->get('artwork');
            if ($artworkFile !== null) {
                $path = $this->fileUploadService->upload($artworkFile, 'planets', $planet->getSlug() . '-artwork');
                $planet->setArtwork($path);
            }

            $errors = $this->validator->validate($planet);
            if (count($errors) === 0) {
                $this->entityManager->persist($planet);
                $this->entityManager->flush();
                $this->addFlash('success', $isEdit ? "Planète mise à jour." : "Planète créée.");
                return $this->redirectToRoute('back_planets_index');
            }

            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('back/planet/form.html.twig', [
            'planet' => $planet,
            'isEdit' => $isEdit,
            'types' => PlanetType::cases(),
        ]);
    }
}
