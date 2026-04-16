<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/back/clients', name: 'back_clients_')]
class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {}

    #[IsGranted('ROLE_USER')]
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query->getString('q', ''));
        $clients = $q !== '' ? $this->repository->search($q) : $this->repository->findAllOrdered();

        return $this->json(array_map(static fn(Client $c) => [
            'id'   => $c->getId(),
            'name' => $c->getName(),
        ], $clients));
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/quick-create', name: 'quick_create', methods: ['POST'])]
    public function quickCreate(Request $request): JsonResponse
    {
        $name = trim($request->request->getString('name'));
        if ($name === '') {
            return $this->json(['error' => 'Le nom est requis.'], 400);
        }

        $existing = $this->repository->findOneBy(['name' => $name]);
        if ($existing) {
            return $this->json(['id' => $existing->getId(), 'name' => $existing->getName()]);
        }

        $client = new Client();
        $client->setName($name);

        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors->get(0)->getMessage()], 422);
        }

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $this->json(['id' => $client->getId(), 'name' => $client->getName()], 201);
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('back/client/index.html.twig', [
            'clients' => $this->repository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        return $this->handleForm($request, new Client(), false);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Client $client): Response
    {
        return $this->handleForm($request, $client, true);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Client $client): Response
    {
        if ($this->isCsrfTokenValid('delete_client_' . $client->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($client);
            $this->entityManager->flush();
            $this->addFlash('success', "Client \"{$client->getName()}\" supprimé.");
        }
        return $this->redirectToRoute('back_clients_index');
    }

    private function handleForm(Request $request, Client $client, bool $isEdit): Response
    {
        if ($request->isMethod('POST')) {
            $client->setName(trim($request->request->getString('name')));

            $errors = $this->validator->validate($client);
            if (count($errors) === 0) {
                $this->entityManager->persist($client);
                $this->entityManager->flush();
                $this->addFlash('success', $isEdit ? 'Client mis à jour.' : 'Client créé.');
                return $this->redirectToRoute('back_clients_index');
            }

            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('back/client/form.html.twig', [
            'client' => $client,
            'isEdit' => $isEdit,
        ]);
    }
}
