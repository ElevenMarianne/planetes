<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Astronaut;
use App\Entity\Planet;
use App\Repository\PlanetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:astronaut:create',
    description: 'Crée un nouvel astronaute (compte utilisateur)',
)]
class CreateAstronautCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PlanetRepository $planetRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('firstName', InputArgument::REQUIRED, 'Prénom')
            ->addArgument('lastName',  InputArgument::REQUIRED, 'Nom de famille')
            ->addArgument('email',     InputArgument::REQUIRED, 'Adresse e-mail')
            ->addOption('planet', 'p', InputOption::VALUE_OPTIONAL, 'Slug de la planète (ex: raccoons-of-asgard)', 'asteroide')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Attribuer le rôle ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $firstName   = trim($input->getArgument('firstName'));
        $lastName    = trim($input->getArgument('lastName'));
        $email       = trim($input->getArgument('email'));
        $planetSlug  = $input->getOption('planet');
        $isAdmin     = $input->getOption('admin');

        // Validation basique de l'e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error("L'adresse e-mail « {$email} » est invalide.");
            return Command::FAILURE;
        }

        // Vérifier l'unicité de l'e-mail
        $existing = $this->entityManager->getRepository(Astronaut::class)->findOneBy(['email' => $email]);
        if ($existing !== null) {
            $io->error("Un astronaute avec l'e-mail « {$email} » existe déjà (id: {$existing->getId()}).");
            return Command::FAILURE;
        }

        // Résoudre la planète
        $planet = $this->planetRepository->findOneBy(['slug' => $planetSlug]);
        if ($planet === null) {
            $available = array_map(
                fn (Planet $p) => $p->getSlug(),
                $this->planetRepository->findAll()
            );
            $io->error("Planète « {$planetSlug} » introuvable. Disponibles : " . implode(', ', $available));
            return Command::FAILURE;
        }

        $astronaut = new Astronaut();
        $astronaut->setFirstName($firstName);
        $astronaut->setLastName($lastName);
        $astronaut->setEmail($email);
        $astronaut->setRoles($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER']);
        $astronaut->setPlanet($planet);
        $astronaut->setIsActive(true);
        $astronaut->setArrivedAt(new \DateTimeImmutable('today'));

        $this->entityManager->persist($astronaut);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Astronaute créé : %s %s <%s> — planète : %s%s (id: %d)',
            $firstName,
            $lastName,
            $email,
            $planet->getName(),
            $isAdmin ? ' — ROLE_ADMIN' : '',
            $astronaut->getId(),
        ));

        return Command::SUCCESS;
    }
}
