<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Grade;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GradeFixtures extends Fixture
{
    private const GRADES = [
        ['slug' => 'rookie',                'name' => 'Rookie',               'minPoints' => 0,     'sortOrder' => 1],
        ['slug' => 'ensign',                'name' => 'Ensign',               'minPoints' => 50,    'sortOrder' => 2],
        ['slug' => 'lieutenant',            'name' => 'Lieutenant',           'minPoints' => 100,   'sortOrder' => 3],
        ['slug' => 'lieutenant_commander', 'name' => 'Lieutenant Commander', 'minPoints' => 200,   'sortOrder' => 4],
        ['slug' => 'commander',            'name' => 'Commander',            'minPoints' => 300,   'sortOrder' => 5],
        ['slug' => 'captain',              'name' => 'Captain',              'minPoints' => 500,   'sortOrder' => 6],
        ['slug' => 'fleet_captain',        'name' => 'Fleet Captain',        'minPoints' => 750,   'sortOrder' => 7],
        ['slug' => 'commodore',            'name' => 'Commodore',            'minPoints' => 1000,  'sortOrder' => 8],
        ['slug' => 'rear_admiral',         'name' => 'Rear Admiral',         'minPoints' => 1500,  'sortOrder' => 9],
        ['slug' => 'vice_admiral',         'name' => 'Vice Admiral',         'minPoints' => 2000,  'sortOrder' => 10],
        ['slug' => 'admiral',              'name' => 'Admiral',              'minPoints' => 3000,  'sortOrder' => 11],
        ['slug' => 'fleet_admiral',        'name' => 'Fleet Admiral',        'minPoints' => 5000,  'sortOrder' => 12],
        ['slug' => 'fleet_admiral_2stars', 'name' => 'Fleet Admiral ★★',    'minPoints' => 10000, 'sortOrder' => 13],
        ['slug' => 'fleet_admiral_3stars', 'name' => 'Fleet Admiral ★★★',   'minPoints' => 15000, 'sortOrder' => 14],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::GRADES as $data) {
            $grade = new Grade();
            $grade->setName($data['name']);
            $grade->setSlug($data['slug']);
            $grade->setMinPoints($data['minPoints']);
            $grade->setSortOrder($data['sortOrder']);
            $manager->persist($grade);
            $this->addReference('grade_' . $data['slug'], $grade);
        }

        $manager->flush();
    }
}
