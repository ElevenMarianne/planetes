<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Planet;
use App\Enum\PlanetType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlanetFixtures extends Fixture
{
    private const PLANETS = [
        [
            'name'   => 'Raccoons of Asgard',
            'slug'   => 'raccoons-of-asgard',
            'color'  => '#ffd639',
            'type'   => PlanetType::MAIN,
            'mantra' => null,
            'photo'  => 'planets/raccoons-of-asgard.webp',
        ],
        [
            'name'   => 'Donuts Panda',
            'slug'   => 'donuts-panda',
            'color'  => '#e92e32',
            'type'   => PlanetType::MAIN,
            'mantra' => null,
            'photo'  => 'planets/donut-factory.webp',
        ],
        [
            'name'   => 'Ducks',
            'slug'   => 'ducks',
            'color'  => '#3ac692',
            'type'   => PlanetType::MAIN,
            'mantra' => null,
            'photo'  => 'planets/duck-invaders.webp',
        ],
        [
            'name'   => 'Les chatons',
            'slug'   => 'les-chatons',
            'color'  => '#4d57a8',
            'type'   => PlanetType::MAIN,
            'mantra' => null,
            'photo'  => 'planets/schizo-cats.webp',
        ],
        [
            'name'   => 'Astéroïde',
            'slug'   => 'asteroide',
            'color'  => '#6B7280',
            'type'   => PlanetType::NEWCOMER,
            'mantra' => null,
            'photo'  => null,
        ],
        [
            'name'   => 'Admin',
            'slug'   => 'admin',
            'color'  => '#6366F1',
            'type'   => PlanetType::REFEREE,
            'mantra' => null,
            'photo'  => null,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PLANETS as $index => $data) {
            $planet = new Planet();
            $planet->setName($data['name']);
            $planet->setSlug($data['slug']);
            $planet->setColor($data['color']);
            $planet->setType($data['type']);
            if ($data['mantra'] !== null) {
                $planet->setMantra($data['mantra']);
            }
            if ($data['photo'] !== null) {
                $planet->setPhoto($data['photo']);
            }
            $manager->persist($planet);
            $this->addReference('planet_' . $index, $planet);
        }

        $manager->flush();
    }
}
