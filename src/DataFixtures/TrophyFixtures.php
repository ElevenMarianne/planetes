<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Trophy;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TrophyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $trophy = new Trophy();
        $trophy->setSlug('best_planet_season');
        $trophy->setName('Planète championne de la saison');
        $trophy->setDescription('Attribué automatiquement à la planète ayant accumulé le plus de points à la clôture d\'une saison.');
        $manager->persist($trophy);
        $this->addReference('trophy_best_planet_season', $trophy);

        $manager->flush();
    }
}
