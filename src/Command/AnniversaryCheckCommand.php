<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\ActivityUniqueName;
use App\Message\SlackAnniversaryNotification;
use App\Repository\ActivityTypeRepository;
use App\Repository\AstronautRepository;
use App\Service\PlanetSeasonPointsService;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:anniversary-check',
    description: "Vérifie les anniversaires d'arrivée et attribue les points d'ancienneté",
)]
class AnniversaryCheckCommand extends Command
{
    public function __construct(
        private readonly AstronautRepository $astronautRepository,
        private readonly ActivityTypeRepository $activityTypeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly SeasonService $seasonService,
        private readonly PlanetSeasonPointsService $planetSeasonPointsService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $today  = new \DateTimeImmutable();
        $season = $this->seasonService->getActiveSeason();

        $astronauts = $this->astronautRepository->findActiveWithAnniversaryToday();

        if (empty($astronauts)) {
            $io->info("Aucun anniversaire d'arrivée aujourd'hui ({$today->format('d/m')}).");
            return Command::SUCCESS;
        }

        $seniorityType = $this->activityTypeRepository->findByUniqueName(ActivityUniqueName::SENIORITY);

        if ($seniorityType === null) {
            $io->error("ActivityType '" . ActivityUniqueName::SENIORITY->value . "' introuvable. Vérifiez les fixtures.");
            return Command::FAILURE;
        }

        foreach ($astronauts as $astronaut) {
            $years  = (int) $astronaut->getArrivedAt()->diff($today)->y;
            $points = $years * $seniorityType->getBasePoints();

            $astronaut->addPoints($points);

            if ($season !== null && $astronaut->getPlanet() !== null) {
                $this->planetSeasonPointsService->addPoints($astronaut->getPlanet(), $season, $points);
            }

            $this->bus->dispatch(new SlackAnniversaryNotification(
                astronautName: $astronaut->getFullName(),
                years: $years,
                points: $points,
                planetName: $astronaut->getPlanet()?->getName(),
            ));

            $io->success(sprintf(
                '%s — %d an%s · +%d pts (%d × %d pts)',
                $astronaut->getFullName(),
                $years,
                $years > 1 ? 's' : '',
                $points,
                $years,
                $seniorityType->getBasePoints(),
            ));
        }

        $this->entityManager->flush();

        $io->info(sprintf('%d anniversaire(s) traité(s).', count($astronauts)));

        return Command::SUCCESS;
    }
}
