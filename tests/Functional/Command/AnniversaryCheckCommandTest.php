<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Command\AnniversaryCheckCommand;
use App\Entity\ActivityType;
use App\Entity\Astronaut;
use App\Enum\ActivityUniqueName;
use App\Enum\PointsTarget;
use App\Message\SlackAnniversaryNotification;
use App\Repository\ActivityTypeRepository;
use App\Repository\AstronautRepository;
use App\Service\PlanetSeasonPointsService;
use App\Service\SeasonService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class AnniversaryCheckCommandTest extends KernelTestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeActivityType(int $basePoints = 50): ActivityType
    {
        $type = new ActivityType();
        $type->setName("Points d'ancienneté");
        $type->setSlug('seniority');
        $type->setBasePoints($basePoints);
        $type->setPointsTarget(PointsTarget::ASTRONAUT_ONLY);
        $type->setUniqueName(ActivityUniqueName::SENIORITY);

        return $type;
    }

    private function makeAstronaut(string $firstName, string $lastName, int $yearsAgo): Astronaut
    {
        $astronaut = new Astronaut();
        $astronaut->setFirstName($firstName);
        $astronaut->setLastName($lastName);
        $astronaut->setEmail(strtolower($firstName) . '.' . strtolower($lastName) . '@eleven-labs.com');
        $astronaut->setArrivedAt(new \DateTimeImmutable("-{$yearsAgo} years"));

        return $astronaut;
    }

    /**
     * Injecte les stubs dans le container et retourne le CommandTester prêt à l'emploi.
     * Utiliser uniquement quand aucun expects() n'est nécessaire sur SeasonService / EntityManager.
     */
    private function buildTester(
        AstronautRepository $astronautRepo,
        ActivityTypeRepository $activityTypeRepo,
    ): CommandTester {
        $container = self::getContainer();

        $container->set(AstronautRepository::class, $astronautRepo);
        $container->set(ActivityTypeRepository::class, $activityTypeRepo);

        $seasonService = $this->createStub(SeasonService::class);
        $seasonService->method('getActiveSeason')->willReturn(null);
        $container->set(SeasonService::class, $seasonService);

        $container->set(EntityManagerInterface::class, $this->createStub(EntityManagerInterface::class));
        $container->set(PlanetSeasonPointsService::class, $this->createStub(PlanetSeasonPointsService::class));

        return new CommandTester($container->get(AnniversaryCheckCommand::class));
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    #[Test]
    public function itReturnsSuccessAndSkipsQueryWhenNoAnniversaryToday(): void
    {
        self::bootKernel();

        $astronautRepo = $this->createStub(AstronautRepository::class);
        $astronautRepo->method('findActiveWithAnniversaryToday')->willReturn([]);

        // Vérification explicite : findByUniqueName ne doit jamais être appelé
        $activityTypeRepo = $this->createMock(ActivityTypeRepository::class);
        $activityTypeRepo->expects($this->never())->method('findByUniqueName');

        $tester = $this->buildTester($astronautRepo, $activityTypeRepo);
        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString("Aucun anniversaire", $tester->getDisplay());
    }

    #[Test]
    public function itReturnsFailureWhenSeniorityActivityTypeIsMissing(): void
    {
        self::bootKernel();

        $astronautRepo = $this->createStub(AstronautRepository::class);
        $astronautRepo->method('findActiveWithAnniversaryToday')->willReturn([$this->makeAstronaut('Ada', 'Lovelace', 2)]);

        $activityTypeRepo = $this->createStub(ActivityTypeRepository::class);
        $activityTypeRepo->method('findByUniqueName')->willReturn(null);

        $tester = $this->buildTester($astronautRepo, $activityTypeRepo);
        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('introuvable', $tester->getDisplay());
    }

    #[Test]
    public function itAwardsCorrectPointsForOneYearAnniversary(): void
    {
        self::bootKernel();

        $astronaut = $this->makeAstronaut('Marie', 'Curie', 1);

        $astronautRepo = $this->createStub(AstronautRepository::class);
        $astronautRepo->method('findActiveWithAnniversaryToday')->willReturn([$astronaut]);

        $activityTypeRepo = $this->createStub(ActivityTypeRepository::class);
        $activityTypeRepo->method('findByUniqueName')->willReturn($this->makeActivityType(50));

        $this->buildTester($astronautRepo, $activityTypeRepo)->execute([]);

        $this->assertSame(50, $astronaut->getTotalPoints()); // 1 an × 50 pts
    }

    #[Test]
    public function itAwardsCorrectPointsForMultipleYears(): void
    {
        self::bootKernel();

        $astronaut = $this->makeAstronaut('Alan', 'Turing', 5);

        $astronautRepo = $this->createStub(AstronautRepository::class);
        $astronautRepo->method('findActiveWithAnniversaryToday')->willReturn([$astronaut]);

        $activityTypeRepo = $this->createStub(ActivityTypeRepository::class);
        $activityTypeRepo->method('findByUniqueName')->willReturn($this->makeActivityType(50));

        $tester = $this->buildTester($astronautRepo, $activityTypeRepo);
        $tester->execute([]);

        $this->assertSame(250, $astronaut->getTotalPoints()); // 5 ans × 50 pts
        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('Alan Turing', $tester->getDisplay());
        $this->assertStringContainsString('250', $tester->getDisplay());
        $this->assertStringContainsString('5 ans', $tester->getDisplay());
    }

    #[Test]
    public function itFlushesEntityManagerAfterAwardingPoints(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $astronautRepo = $this->createStub(AstronautRepository::class);
        $astronautRepo->method('findActiveWithAnniversaryToday')->willReturn([$this->makeAstronaut('Grace', 'Hopper', 3)]);

        $activityTypeRepo = $this->createStub(ActivityTypeRepository::class);
        $activityTypeRepo->method('findByUniqueName')->willReturn($this->makeActivityType(50));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $seasonService = $this->createStub(SeasonService::class);
        $seasonService->method('getActiveSeason')->willReturn(null);

        $container->set(AstronautRepository::class, $astronautRepo);
        $container->set(ActivityTypeRepository::class, $activityTypeRepo);
        $container->set(EntityManagerInterface::class, $em);
        $container->set(SeasonService::class, $seasonService);
        $container->set(PlanetSeasonPointsService::class, $this->createStub(PlanetSeasonPointsService::class));

        $tester = new CommandTester($container->get(AnniversaryCheckCommand::class));
        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    #[Test]
    public function itDispatchesSlackNotificationForEachAnniversary(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $astronautRepo = $this->createStub(AstronautRepository::class);
        $astronautRepo->method('findActiveWithAnniversaryToday')->willReturn([$this->makeAstronaut('Grace', 'Hopper', 3)]);

        $activityTypeRepo = $this->createStub(ActivityTypeRepository::class);
        $activityTypeRepo->method('findByUniqueName')->willReturn($this->makeActivityType(50));

        $seasonService = $this->createStub(SeasonService::class);
        $seasonService->method('getActiveSeason')->willReturn(null);

        $container->set(AstronautRepository::class, $astronautRepo);
        $container->set(ActivityTypeRepository::class, $activityTypeRepo);
        $container->set(SeasonService::class, $seasonService);
        $container->set(EntityManagerInterface::class, $this->createStub(EntityManagerInterface::class));
        $container->set(PlanetSeasonPointsService::class, $this->createStub(PlanetSeasonPointsService::class));

        (new CommandTester($container->get(AnniversaryCheckCommand::class)))->execute([]);

        /** @var InMemoryTransport $transport */
        $transport = $container->get('messenger.transport.async');
        $envelopes = $transport->get();

        $this->assertCount(1, $envelopes);

        /** @var SlackAnniversaryNotification $message */
        $message = $envelopes[0]->getMessage();

        $this->assertInstanceOf(SlackAnniversaryNotification::class, $message);
        $this->assertSame('Grace Hopper', $message->astronautName);
        $this->assertSame(3, $message->years);
        $this->assertSame(150, $message->points); // 3 × 50
        $this->assertNull($message->planetName);
    }

    #[Test]
    public function itDispatchesOneNotificationPerAstronautWhenMultipleAnniversaries(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $astronaut1 = $this->makeAstronaut('Linus', 'Torvalds', 2);
        $astronaut2 = $this->makeAstronaut('Dennis', 'Ritchie', 4);

        $astronautRepo = $this->createStub(AstronautRepository::class);
        $astronautRepo->method('findActiveWithAnniversaryToday')->willReturn([$astronaut1, $astronaut2]);

        $activityTypeRepo = $this->createStub(ActivityTypeRepository::class);
        $activityTypeRepo->method('findByUniqueName')->willReturn($this->makeActivityType(50));

        $seasonService = $this->createStub(SeasonService::class);
        $seasonService->method('getActiveSeason')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $container->set(AstronautRepository::class, $astronautRepo);
        $container->set(ActivityTypeRepository::class, $activityTypeRepo);
        $container->set(SeasonService::class, $seasonService);
        $container->set(EntityManagerInterface::class, $em);
        $container->set(PlanetSeasonPointsService::class, $this->createStub(PlanetSeasonPointsService::class));

        $tester = new CommandTester($container->get(AnniversaryCheckCommand::class));
        $tester->execute([]);

        $this->assertSame(100, $astronaut1->getTotalPoints()); // 2 × 50
        $this->assertSame(200, $astronaut2->getTotalPoints()); // 4 × 50

        /** @var InMemoryTransport $transport */
        $transport = $container->get('messenger.transport.async');
        $this->assertCount(2, $transport->get());

        $this->assertStringContainsString('2 anniversaire(s) traité(s)', $tester->getDisplay());
    }
}
