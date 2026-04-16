<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Repository\AstronautRepository;
use App\Repository\EventParticipantRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_REFEREE')]
#[Route('/back/events', name: 'back_events_')]
class EventController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $repository,
        private readonly AstronautRepository $astronautRepository,
        private readonly EventParticipantRepository $participantRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('back/event/index.html.twig', [
            'events' => $this->repository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $event = new Event();
        if ($request->isMethod('POST')) {
            $event->setName($request->request->getString('name'));
            $event->setDate(new \DateTime($request->request->getString('date')));
            $event->setDescription($request->request->getString('description') ?: null);
            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $this->addFlash('success', 'Événement créé.');
            return $this->redirectToRoute('back_events_attendance', ['id' => $event->getId()]);
        }

        return $this->render('back/event/form.html.twig', ['event' => $event]);
    }

    #[Route('/{id}/attendance', name: 'attendance', requirements: ['id' => '\d+'])]
    public function attendance(Event $event): Response
    {
        $astronauts = $this->astronautRepository->findAllActiveOrdered();
        $participantIds = array_map(
            fn ($ep) => $ep->getAstronaut()->getId(),
            $event->getParticipants()->toArray()
        );

        return $this->render('back/event/attendance.html.twig', [
            'event' => $event,
            'astronauts' => $astronauts,
            'participantIds' => $participantIds,
        ]);
    }

    #[Route('/{id}/toggle-participant', name: 'toggle_participant', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleParticipant(Request $request, Event $event): JsonResponse
    {
        $astronautId = $request->request->getInt('astronautId');
        $astronaut = $this->astronautRepository->find($astronautId);

        if (!$astronaut) {
            return new JsonResponse(['error' => 'Astronaute introuvable'], 404);
        }

        $existing = $this->participantRepository->findByEventAndAstronaut($event, $astronaut);

        if ($existing) {
            $this->entityManager->remove($existing);
            $isPresent = false;
        } else {
            $participant = new EventParticipant();
            $participant->setEvent($event);
            $participant->setAstronaut($astronaut);
            $this->entityManager->persist($participant);
            $isPresent = true;
        }

        $this->entityManager->flush();

        return new JsonResponse(['present' => $isPresent, 'astronautId' => $astronautId]);
    }
}
