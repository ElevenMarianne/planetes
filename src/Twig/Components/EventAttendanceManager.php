<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Astronaut;
use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Entity\Planet;
use App\Message\SlackAttendanceNotification;
use App\Repository\AstronautRepository;
use App\Repository\EventParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/EventAttendanceManager.html.twig')]
class EventAttendanceManager
{
    use DefaultActionTrait;

    #[LiveProp]
    public Event $event;

    #[LiveProp(writable: true)]
    public string $search = '';

    public function __construct(
        private readonly AstronautRepository $astronautRepository,
        private readonly EventParticipantRepository $participantRepository,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $bus,
    ) {}

    public function getFilteredAstronauts(): array
    {
        $all = $this->astronautRepository->findAllActiveOrdered();

        if ($this->search === '') {
            return $all;
        }

        $q = mb_strtolower($this->search);

        return array_filter($all, function (Astronaut $a) use ($q): bool {
            return str_contains(mb_strtolower($a->getFirstName()), $q)
                || str_contains(mb_strtolower($a->getLastName()), $q);
        });
    }

    /** @return Planet[] */
    public function getPlanets(): array
    {
        $planets = [];
        foreach ($this->astronautRepository->findAllActiveOrdered() as $astronaut) {
            $planet = $astronaut->getPlanet();
            if ($planet !== null && !isset($planets[$planet->getId()])) {
                $planets[$planet->getId()] = $planet;
            }
        }
        ksort($planets);
        return array_values($planets);
    }

    public function getParticipantIds(): array
    {
        return array_map(
            fn (EventParticipant $ep) => $ep->getAstronaut()->getId(),
            $this->event->getParticipants()->toArray()
        );
    }

    #[LiveAction]
    public function toggleParticipant(#[LiveArg] int $astronautId): void
    {
        $astronaut = $this->astronautRepository->find($astronautId);
        if (!$astronaut instanceof Astronaut) {
            return;
        }

        $existing = $this->participantRepository->findByEventAndAstronaut($this->event, $astronaut);

        if ($existing !== null) {
            $this->em->remove($existing);
        } else {
            $participant = new EventParticipant();
            $participant->setEvent($this->event);
            $participant->setAstronaut($astronaut);
            $this->em->persist($participant);
        }

        $this->em->flush();

        // Rafraîchir la collection depuis la BDD pour que getParticipantIds() soit à jour
        $this->em->refresh($this->event);
    }

    #[LiveAction]
    public function sendToSlack(): void
    {
        // Initialiser toutes les planètes actives à 0 présents
        $byPlanet = [];
        foreach ($this->getPlanets() as $planet) {
            $byPlanet[$planet->getId()] = ['planet' => $planet->getName(), 'names' => []];
        }

        // Remplir avec les participants
        foreach ($this->event->getParticipants()->toArray() as $ep) {
            $astronaut = $ep->getAstronaut();
            $planet    = $astronaut->getPlanet();
            if ($planet === null || !isset($byPlanet[$planet->getId()])) {
                continue;
            }

            $initial = mb_strtoupper(mb_substr($astronaut->getLastName(), 0, 1));
            $byPlanet[$planet->getId()]['names'][] = $astronaut->getFirstName() . ' ' . $initial . '.';
        }

        $payload = [];
        foreach ($byPlanet as $entry) {
            sort($entry['names']);
            $payload[] = [
                'planet' => $entry['planet'],
                'count'  => count($entry['names']),
                'names'  => implode(', ', $entry['names']),
            ];
        }

        $this->bus->dispatch(new SlackAttendanceNotification(
            eventName: $this->event->getName(),
            byPlanet: $payload,
        ));
    }
}
