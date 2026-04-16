<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\Astronaut;
use App\Form\Front\ProfileEditType;
use App\Repository\ClientRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/profile', name: 'front_profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploadService $fileUploadService,
        private readonly ClientRepository $clientRepository,
    ) {}

    #[Route('', name: '')]
    public function index(): Response
    {
        /** @var Astronaut $astronaut */
        $astronaut = $this->getUser();
        return $this->redirectToRoute('front_astronauts_show', ['id' => $astronaut->getId()]);
    }

    #[Route('/edit', name: '_edit')]
    public function edit(Request $request): Response
    {
        /** @var Astronaut $astronaut */
        $astronaut = $this->getUser();
        $form = $this->createForm(ProfileEditType::class, $astronaut);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                if ($astronaut->getPhoto()) {
                    $this->fileUploadService->remove($astronaut->getPhoto());
                }
                $path = $this->fileUploadService->upload($photoFile, 'astronauts');
                $astronaut->setPhoto($path);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('front_profile');
        }

        $clients = array_map(
            fn ($c) => ['id' => $c->getId(), 'name' => $c->getName()],
            $this->clientRepository->findAllOrdered(),
        );

        return $this->render('front/profile/edit.html.twig', [
            'form'      => $form,
            'astronaut' => $astronaut,
            'clients'   => $clients,
        ]);
    }
}
