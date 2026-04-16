<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Astronaut;
use App\Enum\Squad;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AstronautFixtures extends Fixture implements DependentFixtureInterface
{
    // planet index: 0=Raccoons, 1=Donuts Panda, 2=Ducks, 3=Les chatons
    private const ASTRONAUTS = [
        // ── Raccoons of Asgard ──────────────────────────────────────────────
        ['firstName' => 'Thomas',     'lastName' => 'Dupont',          'email' => 'tdupont@eleven-labs.com',          'roles' => ['ROLE_ADMIN'], 'planet' => 0, 'squad' => Squad::PARIS,      'arrivedAt' => '2020-09-01'],
        ['firstName' => 'Julie',      'lastName' => 'Martin',          'email' => 'jmartin@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 0, 'squad' => Squad::PARIS,      'arrivedAt' => '2021-01-11'],
        ['firstName' => 'Alexandre',  'lastName' => 'Moreau',          'email' => 'amoreau@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 0, 'squad' => Squad::NANTES,     'arrivedAt' => '2018-06-18'],
        ['firstName' => 'Camille',    'lastName' => 'Bernard',         'email' => 'cbernard@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 0, 'squad' => Squad::NANTES,     'arrivedAt' => '2022-04-04'],
        ['firstName' => 'Nicolas',    'lastName' => 'Petit',           'email' => 'npetit@eleven-labs.com',           'roles' => ['ROLE_USER'],  'planet' => 0, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2023-02-13'],
        ['firstName' => 'Sarah',      'lastName' => 'Leclerc',         'email' => 'sleclerc@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 0, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2020-11-02'],
        ['firstName' => 'Julien',     'lastName' => 'Robert',          'email' => 'jrobert@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 0, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2019-07-22'],
        ['firstName' => 'Emma',       'lastName' => 'Durand',          'email' => 'edurand@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 0, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2024-01-08'],
        ['firstName' => 'Maxime',     'lastName' => 'Simon',           'email' => 'msimon@eleven-labs.com',           'roles' => ['ROLE_USER'],  'planet' => 0, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2021-09-06'],

        // ── Donuts Panda ────────────────────────────────────────────────────
        ['firstName' => 'Antoine',    'lastName' => 'Lefebvre',        'email' => 'alefebvre@eleven-labs.com',        'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::PARIS,      'arrivedAt' => '2017-10-02'],
        ['firstName' => 'Léa',        'lastName' => 'Rousseau',        'email' => 'lrousseau@eleven-labs.com',        'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::PARIS,      'arrivedAt' => '2022-06-20'],
        ['firstName' => 'Baptiste',   'lastName' => 'Laurent',         'email' => 'blaurent@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::PARIS,      'arrivedAt' => '2020-03-16'],
        ['firstName' => 'Marine',     'lastName' => 'Fontaine',        'email' => 'mfontaine@eleven-labs.com',        'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::NANTES,     'arrivedAt' => '2023-09-04'],
        ['firstName' => 'Kevin',      'lastName' => 'Girard',          'email' => 'kgirard@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::NANTES,     'arrivedAt' => '2019-01-14'],
        ['firstName' => 'Aurélie',    'lastName' => 'Morel',           'email' => 'amorel@eleven-labs.com',           'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2021-05-03'],
        ['firstName' => 'Romain',     'lastName' => 'Bonneau',         'email' => 'rbonneau@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2018-11-19'],
        ['firstName' => 'Clara',      'lastName' => 'Faure',           'email' => 'cfaure@eleven-labs.com',           'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2024-03-11'],
        ['firstName' => 'Mathieu',    'lastName' => 'Denis',           'email' => 'mdenis@eleven-labs.com',           'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2022-10-24'],
        ['firstName' => 'Pauline',    'lastName' => 'Lacroix',         'email' => 'placroix@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 1, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2020-07-06'],

        // ── Ducks ───────────────────────────────────────────────────────────
        ['firstName' => 'François',   'lastName' => 'Chevalier',       'email' => 'fchevalier@eleven-labs.com',       'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::PARIS,      'arrivedAt' => '2016-04-04'],
        ['firstName' => 'Amélie',     'lastName' => 'Garnier',         'email' => 'agarnier@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::PARIS,      'arrivedAt' => '2023-01-23'],
        ['firstName' => 'Quentin',    'lastName' => 'Blanc',           'email' => 'qblanc@eleven-labs.com',           'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::PARIS,      'arrivedAt' => '2021-11-15'],
        ['firstName' => 'Elise',      'lastName' => 'Guerin',          'email' => 'eguerin@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::NANTES,     'arrivedAt' => '2019-08-26'],
        ['firstName' => 'Pierre',     'lastName' => 'Muller',          'email' => 'pmuller@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::NANTES,     'arrivedAt' => '2020-02-10'],
        ['firstName' => 'Laura',      'lastName' => 'Henry',           'email' => 'lhenry@eleven-labs.com',           'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2022-08-29'],
        ['firstName' => 'Vincent',    'lastName' => 'Gauthier',        'email' => 'vgauthier@eleven-labs.com',        'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2018-03-07'],
        ['firstName' => 'Chloé',      'lastName' => 'Perrin',          'email' => 'cperrin@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2024-06-17'],
        ['firstName' => 'Sébastien',  'lastName' => 'Renard',          'email' => 'srenard@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2017-12-04'],
        ['firstName' => 'Manon',      'lastName' => 'Lecomte',         'email' => 'mlecomte@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 2, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2023-05-22'],

        // ── Astéroïde (en attente d'affectation) ────────────────────────────
        ['firstName' => 'Hugo',       'lastName' => 'Vasseur',         'email' => 'hvasseur@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 4, 'squad' => Squad::PARIS,      'arrivedAt' => '2025-10-06'],
        ['firstName' => 'Nadia',      'lastName' => 'Khalil',          'email' => 'nkhalil@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 4, 'squad' => Squad::NANTES,     'arrivedAt' => '2025-11-17'],

        // ── Les chatons ─────────────────────────────────────────────────────
        ['firstName' => 'Alexis',     'lastName' => 'Mercier',         'email' => 'amercier@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::PARIS,      'arrivedAt' => '2020-06-01'],
        ['firstName' => 'Lucie',      'lastName' => 'Dupuis',          'email' => 'ldupuis@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::PARIS,      'arrivedAt' => '2019-04-15'],
        ['firstName' => 'Damien',     'lastName' => 'Lemaire',         'email' => 'dlemaire@eleven-labs.com',         'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::PARIS,      'arrivedAt' => '2021-03-22'],
        ['firstName' => 'Céline',     'lastName' => 'Fournier',        'email' => 'cfournier@eleven-labs.com',        'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::NANTES,     'arrivedAt' => '2022-01-10'],
        ['firstName' => 'Valentin',   'lastName' => 'Adam',            'email' => 'vadam@eleven-labs.com',            'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::NANTES,     'arrivedAt' => '2018-09-03'],
        ['firstName' => 'Inès',       'lastName' => 'Masson',          'email' => 'imasson@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2023-07-31'],
        ['firstName' => 'Thibault',   'lastName' => 'Roy',             'email' => 'troy@eleven-labs.com',             'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2020-10-19'],
        ['firstName' => 'Charlotte',  'lastName' => 'Bourgeois',       'email' => 'cbourgeois@eleven-labs.com',       'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2024-02-26'],
        ['firstName' => 'Raphaël',    'lastName' => 'Giraud',          'email' => 'rgiraud@eleven-labs.com',          'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2017-05-08'],
        ['firstName' => 'Anaïs',      'lastName' => 'Bertrand',        'email' => 'abertrand@eleven-labs.com',        'roles' => ['ROLE_USER'],  'planet' => 3, 'squad' => Squad::PROVINCIAL, 'arrivedAt' => '2022-12-05'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ASTRONAUTS as $i => $data) {
            $astronaut = new Astronaut();
            $astronaut->setFirstName($data['firstName']);
            $astronaut->setLastName($data['lastName']);
            $astronaut->setEmail($data['email']);
            $astronaut->setRoles($data['roles']);
            $astronaut->setIsActive(true);
            $astronaut->setSquad($data['squad']);
            $astronaut->setArrivedAt(new \DateTimeImmutable($data['arrivedAt']));

            /** @var \App\Entity\Planet $planet */
            $planet = $this->getReference('planet_' . $data['planet'], \App\Entity\Planet::class);
            $astronaut->setPlanet($planet);

            $manager->persist($astronaut);
            $this->addReference('astronaut_' . $i, $astronaut);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [PlanetFixtures::class];
    }
}
