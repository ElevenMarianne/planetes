<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ActivityType;
use App\Enum\ActivityUniqueName;
use App\Enum\PointsTarget;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ActivityTypeFixtures extends Fixture
{
    private const TYPES = [
        // Challenges (points pour la planète uniquement, sans bonus)
        ['slug' => 'challenge_1st',  'name' => 'Challenge — 1ère place',  'points' => 100, 'multi' => false, 'target' => PointsTarget::PLANET_ONLY,    'desc' => 'Victoire d\'un challenge interne'],
        ['slug' => 'challenge_2nd',  'name' => 'Challenge — 2ème place',  'points' => 75,  'multi' => false, 'target' => PointsTarget::PLANET_ONLY,    'desc' => '2ème place d\'un challenge interne'],
        ['slug' => 'challenge_3rd',  'name' => 'Challenge — 3ème place',  'points' => 50,  'multi' => false, 'target' => PointsTarget::PLANET_ONLY,    'desc' => '3ème place d\'un challenge interne'],
        ['slug' => 'challenge_4th',  'name' => 'Challenge — 4ème place',  'points' => 25,  'multi' => false, 'target' => PointsTarget::PLANET_ONLY,    'desc' => '4ème place d\'un challenge interne'],

        // Contributions (astronaute + planète)
        ['slug' => 'blog_solo',      'name' => 'Article de blog (solo)',   'points' => 75,  'multi' => false, 'target' => PointsTarget::BOTH,           'desc' => 'Rédaction d\'un article de blog seul'],
        ['slug' => 'blog_duo',       'name' => 'Article de blog (duo)',    'points' => 40,  'multi' => true,  'target' => PointsTarget::BOTH,           'desc' => 'Rédaction d\'un article de blog à deux (40 pts chacun)'],
        ['slug' => 'tech_interview', 'name' => 'Entretien tech',           'points' => 25,  'multi' => false, 'target' => PointsTarget::BOTH,           'desc' => 'Participation à un entretien technique'],

        // Talks
        ['slug' => 'talk_external',  'name' => 'Talk externe',             'points' => 150, 'multi' => false, 'target' => PointsTarget::BOTH,           'desc' => 'Présentation dans une conférence externe'],
        ['slug' => 'talk_internal',  'name' => 'Talk interne',             'points' => 100, 'multi' => false, 'target' => PointsTarget::BOTH,           'desc' => 'Présentation en interne'],

        // Workshops
        ['slug' => 'workshop_solo',  'name' => 'Workshop (solo)',          'points' => 100, 'multi' => false, 'target' => PointsTarget::BOTH,           'desc' => 'Animation d\'un workshop seul'],
        ['slug' => 'workshop_duo',   'name' => 'Workshop (duo)',           'points' => 50,  'multi' => true,  'target' => PointsTarget::BOTH,           'desc' => 'Animation d\'un workshop à deux (50 pts chacun)'],

        // Events
        ['slug' => 'demo_open_mic',  'name' => 'Demo / Open mic',         'points' => 25,  'multi' => false, 'target' => PointsTarget::BOTH,           'desc' => 'Présentation courte en demo ou open mic'],

        // Podcast
        ['slug' => 'podcast_host',   'name' => 'Animation podcast',       'points' => 100, 'multi' => false, 'target' => PointsTarget::BOTH,           'desc' => 'Animation d\'un épisode de podcast'],
        ['slug' => 'podcast_guest',  'name' => 'Participation podcast',   'points' => 25,  'multi' => false, 'target' => PointsTarget::BOTH,           'desc' => 'Participation en tant qu\'invité au podcast'],

        // Codev
        ['slug' => 'codev_host',     'name' => 'Animation co-dev',        'points' => 25,  'multi' => false, 'target' => PointsTarget::BOTH,           'desc' => 'Animation d\'une session de code review / co-dev'],

        // Projets internes
        ['slug' => 'internal_project_s', 'name' => 'Projet interne (S)',  'points' => 100, 'multi' => true,  'target' => PointsTarget::BOTH,           'desc' => 'Contribution à un projet interne — niveau S'],
        ['slug' => 'internal_project_m', 'name' => 'Projet interne (M)',  'points' => 250, 'multi' => true,  'target' => PointsTarget::BOTH,           'desc' => 'Contribution à un projet interne — niveau M'],
        ['slug' => 'internal_project_l', 'name' => 'Projet interne (L)',  'points' => 500, 'multi' => true,  'target' => PointsTarget::BOTH,           'desc' => 'Contribution à un projet interne — niveau L'],
        ['slug' => 'internal_project_xl','name' => 'Projet interne (XL)', 'points' => 750, 'multi' => true,  'target' => PointsTarget::BOTH,           'desc' => 'Contribution à un projet interne — niveau XL'],

        // Ancienneté (astronaute uniquement, ne compte pas pour la planète)
        ['slug' => 'seniority', 'name' => 'Points d\'ancienneté', 'points' => 50, 'multi' => false, 'target' => PointsTarget::ASTRONAUT_ONLY, 'desc' => 'Points attribués selon l\'ancienneté (50 pts × nombre d\'années)', 'uniqueName' => ActivityUniqueName::SENIORITY],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TYPES as $data) {
            $type = new ActivityType();
            $type->setName($data['name']);
            $type->setSlug($data['slug']);
            $type->setBasePoints($data['points']);
            $type->setDescription($data['desc']);
            $type->setAllowsMultipleParticipants($data['multi']);
            $type->setPointsTarget($data['target']);
            $type->setUniqueName($data['uniqueName'] ?? null);
            $type->setIsActive(true);
            $manager->persist($type);
            $this->addReference('activity_type_' . $data['slug'], $type);
        }

        $manager->flush();
    }
}
