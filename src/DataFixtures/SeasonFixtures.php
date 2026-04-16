<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SeasonFixtures extends Fixture
{
    private const FIRST_SEASON_YEAR = 2022;

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();
        // La saison commence en septembre : si on est avant septembre, la saison courante a débuté l'année précédente
        $currentSeasonStartYear = (int) $now->format('n') >= 9
            ? (int) $now->format('Y')
            : (int) $now->format('Y') - 1;

        for ($startYear = self::FIRST_SEASON_YEAR; $startYear <= $currentSeasonStartYear; ++$startYear) {
            $endYear   = $startYear + 1;
            $isCurrent = $startYear === $currentSeasonStartYear;
            $ref       = $isCurrent ? 'season_current' : "season_{$startYear}";

            $season = new Season();
            $season->setName("Saison {$startYear}-{$endYear}");
            $season->setStartDate(new \DateTimeImmutable("{$startYear}-09-01"));
            $season->setEndDate(new \DateTimeImmutable("{$endYear}-08-31"));
            $season->setIsActive($isCurrent);
            $manager->persist($season);
            $this->addReference($ref, $season);
        }

        $manager->flush();
    }
}
