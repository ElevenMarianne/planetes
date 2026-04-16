<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Astronaut;
use App\Entity\Planet;
use App\Entity\PlanetSeasonPoints;
use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Génère des activités fictives + PlanetSeasonPoints pour les 3 saisons.
 *
 * Classement cible (toutes saisons) :
 *   1. Raccoons of Asgard (planet 0)
 *   2. Donuts Panda       (planet 1)
 *   3. Ducks              (planet 2)
 *   4. Les chatons        (planet 3)
 *
 * Totaux approximatifs par saison :
 *   2023-2024 : Raccoons ~1360, Donuts ~1040, Ducks ~750, Chatons ~490
 *   2024-2025 : Raccoons ~1715, Donuts ~1215, Ducks ~965, Chatons ~615
 *   2025-2026 : Raccoons ~925,  Donuts ~700,  Ducks ~540, Chatons ~315
 */
class HistoricalDataFixtures extends Fixture implements DependentFixtureInterface
{
    // [type_slug, astronaut_indexes[], points, date]
    private const DATA = [
        // ────────────────────────────────────────────────────────────
        // SAISON 2022-2023
        // ────────────────────────────────────────────────────────────
        'season_2022' => [
            0 => [ // Raccoons of Asgard — ~1180
                ['internal_project_l',  [0,1,2],    500, '2022-10-15'],
                ['talk_external',       [3],         150, '2022-11-10'],
                ['challenge_1st',       [4],         100, '2022-12-02'],
                ['talk_internal',       [5],         100, '2023-01-20'],
                ['blog_solo',           [6],          75, '2023-02-14'],
                ['workshop_solo',       [7],         100, '2023-03-08'],
                ['blog_duo',            [8,9],        40, '2023-04-10'],
                ['podcast_host',        [0],         100, '2023-05-05'],
                ['demo_open_mic',       [1],          25, '2023-06-15'],
            ],
            1 => [ // Donuts Panda — ~870
                ['internal_project_m',  [10,11],    250, '2022-10-20'],
                ['talk_external',       [12],        150, '2022-11-18'],
                ['challenge_2nd',       [13],         75, '2022-12-02'],
                ['talk_internal',       [14],        100, '2023-01-12'],
                ['blog_solo',           [15],         75, '2023-02-22'],
                ['workshop_duo',        [16,17],      50, '2023-03-20'],
                ['blog_duo',            [18,19],      40, '2023-04-25'],
                ['podcast_guest',       [10],         25, '2023-06-01'],
                ['demo_open_mic',       [11],         25, '2023-06-28'],
                ['codev_host',          [12],         25, '2023-07-10'],
                ['tech_interview',      [13],         25, '2023-07-25'],
            ],
            2 => [ // Ducks — ~620
                ['internal_project_s',  [20,21],    100, '2022-11-05'],
                ['talk_external',       [22],        150, '2022-12-08'],
                ['challenge_3rd',       [23],         50, '2022-12-02'],
                ['talk_internal',       [24],        100, '2023-02-05'],
                ['blog_solo',           [25],         75, '2023-03-15'],
                ['workshop_solo',       [26],        100, '2023-04-18'],
                ['codev_host',          [27],         25, '2023-05-20'],
                ['demo_open_mic',       [28],         25, '2023-06-22'],
            ],
            3 => [ // Les chatons — ~380
                ['blog_solo',           [30],         75, '2022-11-22'],
                ['talk_internal',       [31],        100, '2023-01-08'],
                ['challenge_4th',       [32],         25, '2022-12-02'],
                ['blog_duo',            [33,34],      40, '2023-02-18'],
                ['podcast_guest',       [35],         25, '2023-03-25'],
                ['demo_open_mic',       [36],         25, '2023-04-28'],
                ['tech_interview',      [37],         25, '2023-05-15'],
                ['codev_host',          [38],         25, '2023-06-05'],
                ['blog_solo',           [39],         75, '2023-07-01'],
            ],
        ],

        // ────────────────────────────────────────────────────────────
        // SAISON 2023-2024
        // ────────────────────────────────────────────────────────────
        'season_2023' => [
            0 => [ // Raccoons of Asgard — cible ~1360
                ['internal_project_xl', [0,1,2],   750, '2023-10-20'],
                ['talk_external',       [1],        150, '2023-11-08'],
                ['challenge_1st',       [2],        100, '2023-11-20'],
                ['talk_internal',       [3],        100, '2023-12-05'],
                ['blog_solo',           [4],         75, '2024-01-15'],
                ['workshop_solo',       [5],        100, '2024-02-10'],
                ['blog_duo',            [6,7],       40, '2024-03-01'],
                ['demo_open_mic',       [8],         25, '2024-04-12'],
                ['codev_host',          [9],         25, '2024-05-05'],
            ],
            1 => [ // Donuts Panda — cible ~1040
                ['internal_project_l',  [10,11,12], 500, '2023-10-25'],
                ['talk_external',       [13],       150, '2023-11-14'],
                ['challenge_2nd',       [14],        75, '2023-11-20'],
                ['talk_internal',       [15],       100, '2024-01-08'],
                ['blog_solo',           [16],        75, '2024-02-14'],
                ['workshop_duo',        [17,18],     50, '2024-03-05'],
                ['blog_duo',            [19,10],     40, '2024-04-01'],
                ['podcast_guest',       [11],        25, '2024-05-10'],
                ['demo_open_mic',       [12],        25, '2024-06-02'],
            ],
            2 => [ // Ducks — cible ~750
                ['internal_project_m',  [20,21],    250, '2023-11-05'],
                ['talk_external',       [22],       150, '2023-12-10'],
                ['talk_internal',       [23],       100, '2024-01-20'],
                ['challenge_3rd',       [24],        50, '2024-02-08'],
                ['blog_solo',           [25],        75, '2024-03-15'],
                ['workshop_solo',       [26],       100, '2024-04-20'],
                ['codev_host',          [27],        25, '2024-05-25'],
            ],
            3 => [ // Les chatons — cible ~490
                ['blog_solo',           [30],        75, '2023-11-18'],
                ['talk_internal',       [31],       100, '2023-12-15'],
                ['blog_duo',            [32,33],     40, '2024-01-25'],
                ['podcast_guest',       [34],        25, '2024-02-20'],
                ['workshop_duo',        [35,36],     50, '2024-03-10'],
                ['challenge_4th',       [37],        25, '2024-04-05'],
                ['demo_open_mic',       [38],        25, '2024-05-08'],
                ['tech_interview',      [39],        25, '2024-05-22'],
                ['codev_host',          [30],        25, '2024-06-10'],
                ['blog_solo',           [31],        75, '2024-06-25'],
                ['podcast_guest',       [32],        25, '2024-07-05'],
            ],
        ],

        // ────────────────────────────────────────────────────────────
        // SAISON 2024-2025
        // ────────────────────────────────────────────────────────────
        'season_2024' => [
            0 => [ // Raccoons of Asgard — cible ~1715
                ['internal_project_xl', [0,1,2,3],  750, '2024-10-10'],
                ['talk_external',       [0],         150, '2024-09-25'],
                ['internal_project_m',  [4,5],       250, '2024-11-20'],
                ['challenge_1st',       [1],         100, '2025-01-08'],
                ['podcast_host',        [2],         100, '2025-01-25'],
                ['workshop_solo',       [3],         100, '2025-02-14'],
                ['blog_solo',           [4],          75, '2025-03-08'],
                ['talk_internal',       [5],         100, '2025-04-05'],
                ['blog_duo',            [6,7],        40, '2025-05-10'],
                ['demo_open_mic',       [8],          25, '2025-05-28'],
                ['codev_host',          [9],          25, '2025-06-15'],
            ],
            1 => [ // Donuts Panda — cible ~1215
                ['internal_project_l',  [10,11,12], 500, '2024-10-05'],
                ['talk_external',       [13],        150, '2024-11-02'],
                ['challenge_2nd',       [14],         75, '2024-11-20'],
                ['podcast_host',        [15],        100, '2024-12-10'],
                ['blog_solo',           [16],         75, '2025-01-15'],
                ['talk_internal',       [17],        100, '2025-02-01'],
                ['workshop_solo',       [18],        100, '2025-03-15'],
                ['blog_duo',            [19,10],      40, '2025-04-08'],
                ['challenge_3rd',       [11],         50, '2025-04-25'],
                ['demo_open_mic',       [12],         25, '2025-05-20'],
            ],
            2 => [ // Ducks — cible ~965
                ['internal_project_m',  [20,21,22], 250, '2024-10-15'],
                ['talk_external',       [23],        150, '2024-11-08'],
                ['challenge_3rd',       [24],         50, '2024-11-20'],
                ['blog_solo',           [25],         75, '2024-12-05'],
                ['talk_internal',       [26],        100, '2025-01-20'],
                ['workshop_duo',        [27,28],      50, '2025-02-10'],
                ['podcast_host',        [29],        100, '2025-03-01'],
                ['blog_duo',            [20,21],      40, '2025-04-12'],
                ['challenge_4th',       [22],         25, '2025-05-01'],
                ['blog_solo',           [23],         75, '2025-05-25'],
                ['codev_host',          [24],         25, '2025-06-10'],
                ['demo_open_mic',       [25],         25, '2025-06-28'],
            ],
            3 => [ // Les chatons — cible ~615
                ['blog_solo',           [30],         75, '2024-09-28'],
                ['talk_internal',       [31],        100, '2024-10-20'],
                ['challenge_4th',       [32],         25, '2024-11-20'],
                ['blog_duo',            [33,34],      40, '2024-12-15'],
                ['podcast_guest',       [35],         25, '2025-01-08'],
                ['workshop_duo',        [36,37],      50, '2025-02-05'],
                ['demo_open_mic',       [38],         25, '2025-02-25'],
                ['tech_interview',      [39],         25, '2025-03-18'],
                ['codev_host',          [30],         25, '2025-04-08'],
                ['blog_solo',           [31],         75, '2025-04-28'],
                ['podcast_guest',       [32],         25, '2025-05-15'],
                ['internal_project_s',  [33,34],     100, '2025-06-05'],
                ['demo_open_mic',       [35],         25, '2025-06-25'],
            ],
        ],

        // ────────────────────────────────────────────────────────────
        // SAISON 2025-2026 (en cours)
        // ────────────────────────────────────────────────────────────
        'season_current' => [
            0 => [ // Raccoons of Asgard — ~3200
                ['internal_project_xl', [0,1,2],    750, '2025-09-20'],
                ['talk_external',       [3],         150, '2025-09-25'],
                ['internal_project_l',  [0,1],       500, '2025-10-10'],
                ['challenge_1st',       [4],         100, '2025-10-18'],
                ['podcast_host',        [5],         100, '2025-10-28'],
                ['talk_internal',       [6],         100, '2025-11-05'],
                ['workshop_solo',       [7],         100, '2025-11-12'],
                ['blog_solo',           [8],          75, '2025-11-20'],
                ['blog_duo',            [9,0],        40, '2025-11-28'],
                ['codev_host',          [1],          25, '2025-12-05'],
                ['demo_open_mic',       [2],          25, '2025-12-12'],
                ['tech_interview',      [3],          25, '2025-12-18'],
                ['talk_internal',       [4],         100, '2025-12-22'],
                ['blog_solo',           [5],          75, '2026-01-08'],
                ['podcast_guest',       [6],          25, '2026-01-15'],
                ['workshop_duo',        [7,8],        50, '2026-01-22'],
                ['blog_duo',            [9,0],        40, '2026-01-29'],
                ['talk_external',       [1],         150, '2026-02-05'],
                ['internal_project_m',  [2,3],       250, '2026-02-12'],
                ['demo_open_mic',       [4],          25, '2026-02-19'],
                ['codev_host',          [5],          25, '2026-02-26'],
                ['blog_solo',           [6],          75, '2026-03-05'],
                ['talk_internal',       [7],         100, '2026-03-12'],
                ['podcast_host',        [8],         100, '2026-03-19'],
                ['challenge_1st',       [9],         100, '2026-03-26'],
                ['workshop_solo',       [0],         100, '2026-04-02'],
                ['blog_duo',            [1,2],        40, '2026-04-05'],
                ['tech_interview',      [3],          25, '2026-04-08'],
                ['demo_open_mic',       [4],          25, '2026-04-10'],
                ['podcast_guest',       [5],          25, '2026-04-12'],
            ],
            1 => [ // Donuts Panda — cible ~700
                ['internal_project_m',  [10,11],    250, '2025-10-20'],
                ['talk_external',       [12],        150, '2025-11-08'],
                ['challenge_2nd',       [13],         75, '2025-11-20'],
                ['blog_solo',           [14],         75, '2025-12-10'],
                ['workshop_solo',       [15],        100, '2026-01-20'],
                ['demo_open_mic',       [16],         25, '2026-02-10'],
                ['codev_host',          [17],         25, '2026-03-05'],
            ],
            2 => [ // Ducks — cible ~540
                ['blog_solo',           [20],         75, '2025-10-05'],
                ['talk_internal',       [21],        100, '2025-11-14'],
                ['challenge_3rd',       [22],         50, '2025-11-20'],
                ['workshop_duo',        [23,24],      50, '2025-12-15'],
                ['podcast_guest',       [25],         25, '2026-01-10'],
                ['blog_duo',            [26,27],      40, '2026-02-01'],
                ['internal_project_s',  [20,21],     100, '2026-02-25'],
                ['demo_open_mic',       [22],         25, '2026-03-20'],
                ['blog_solo',           [23],         75, '2026-03-30'],
            ],
            3 => [ // Les chatons — cible ~315
                ['blog_solo',           [30],         75, '2025-10-15'],
                ['challenge_4th',       [31],         25, '2025-11-20'],
                ['talk_internal',       [32],        100, '2025-12-05'],
                ['blog_duo',            [33,34],      40, '2026-01-08'],
                ['demo_open_mic',       [35],         25, '2026-02-05'],
                ['podcast_guest',       [36],         25, '2026-03-01'],
                ['codev_host',          [37],         25, '2026-03-25'],
            ],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $pspAccumulator = [];

        foreach (self::DATA as $seasonRef => $planetData) {
            /** @var Season $season */
            $season = $this->getReference($seasonRef, Season::class);

            foreach ($planetData as $planetIndex => $activities) {
                /** @var Planet $planet */
                $planet = $this->getReference('planet_' . $planetIndex, Planet::class);
                $pspAccumulator[$seasonRef][$planetIndex] = 0;

                foreach ($activities as [$typeSlug, $astronautIndexes, $points, $date]) {
                    $activity = new Activity();
                    $activity->setType($this->getReference('activity_type_' . $typeSlug, \App\Entity\ActivityType::class));
                    $activity->setSeason($season);
                    $activity->setPlanet($planet);
                    $activity->setPoints($points);

                    foreach ($astronautIndexes as $astronautIndex) {
                        /** @var Astronaut $astronaut */
                        $astronaut = $this->getReference('astronaut_' . $astronautIndex, Astronaut::class);
                        $activity->addAstronaut($astronaut);
                        $astronaut->setTotalPoints($astronaut->getTotalPoints() + $points);
                    }

                    $activity->setCreatedAt(new \DateTime($date));
                    $activity->setUpdatedAt(new \DateTime($date));

                    $manager->persist($activity);
                    $pspAccumulator[$seasonRef][$planetIndex] += $points;
                }
            }
        }

        $manager->flush();

        foreach ($pspAccumulator as $seasonRef => $planetPoints) {
            /** @var Season $season */
            $season = $this->getReference($seasonRef, Season::class);

            foreach ($planetPoints as $planetIndex => $totalPoints) {
                /** @var Planet $planet */
                $planet = $this->getReference('planet_' . $planetIndex, Planet::class);

                $psp = new PlanetSeasonPoints();
                $psp->setSeason($season);
                $psp->setPlanet($planet);
                $psp->setTotalPoints($totalPoints);
                $manager->persist($psp);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SeasonFixtures::class,
            PlanetFixtures::class,
            AstronautFixtures::class,
            ActivityTypeFixtures::class,
        ];
    }
}
