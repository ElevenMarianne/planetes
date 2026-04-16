<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\Planet;
use App\Enum\PlanetType;
use App\Repository\ActivityRepository;
use App\Repository\AstronautRepository;
use App\Repository\PlanetRepository;
use App\Repository\PlanetSeasonPointsRepository;
use App\Repository\SeasonRepository;
use App\Service\SeasonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/planets', name: 'front_planets')]
class PlanetController extends AbstractController
{
    private const SEASONS_IN_TABS = 3;

    public function __construct(
        private readonly PlanetRepository $planetRepository,
        private readonly AstronautRepository $astronautRepository,
        private readonly ActivityRepository $activityRepository,
        private readonly PlanetSeasonPointsRepository $pspRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly SeasonService $seasonService,
    ) {}

    #[Route('', name: '')]
    public function index(Request $request): Response
    {
        $planets    = $this->planetRepository->findCompetitors();
        $allSeasons = $this->seasonRepository->findAllOrdered(); // triées DESC

        // Les 3 plus récentes s'affichent en onglets, les autres vont aux archives
        $tabSeasons      = array_slice($allSeasons, 0, self::SEASONS_IN_TABS);
        $hasArchives     = count($allSeasons) > self::SEASONS_IN_TABS;

        // Saison sélectionnée : ?season=ID, ou active, ou la plus récente
        $seasonId       = $request->query->getInt('season', 0) ?: null;
        $selectedSeason = $seasonId
            ? $this->seasonRepository->find($seasonId)
            : $this->seasonService->getActiveSeason();

        // Si la saison demandée explicitement via ?season=ID n'est pas dans les onglets, rediriger vers archives
        if ($seasonId && $selectedSeason && !in_array($selectedSeason, $tabSeasons, true)) {
            return $this->redirectToRoute('front_planets_archives');
        }

        // Si la saison active est archivée (hors onglets), fallback sur la saison la plus récente en onglet
        if ($selectedSeason && !in_array($selectedSeason, $tabSeasons, true)) {
            $selectedSeason = $tabSeasons[0] ?? null;
        }

        $seasonPointsMap = [];
        $totalPoints     = 0;
        if ($selectedSeason) {
            foreach ($planets as $planet) {
                $psp = $this->pspRepository->findByPlanetAndSeason($planet, $selectedSeason);
                $pts = $psp ? $psp->getTotalPoints() : 0;
                $seasonPointsMap[$planet->getId()] = $pts;
                $totalPoints += $pts;
            }
        }

        uasort($planets, static function ($a, $b) use ($seasonPointsMap) {
            return ($seasonPointsMap[$b->getId()] ?? 0) <=> ($seasonPointsMap[$a->getId()] ?? 0);
        });
        $planets = array_values($planets);

        $maxPoints = max(array_values($seasonPointsMap) ?: [1]);

        $unassignedAstronauts = $this->astronautRepository->findActiveByPlanetType(PlanetType::NEWCOMER);

        return $this->render('front/planet/index.html.twig', [
            'planets'              => $planets,
            'season'               => $selectedSeason,
            'tabSeasons'           => $tabSeasons,
            'hasArchives'          => $hasArchives,
            'seasonPointsMap'      => $seasonPointsMap,
            'totalPoints'          => $totalPoints,
            'maxPoints'            => $maxPoints,
            'unassignedAstronauts' => $unassignedAstronauts,
        ]);
    }

    #[Route('/archives', name: '_archives')]
    public function archives(): Response
    {
        $allSeasons = $this->seasonRepository->findAllOrdered();
        $planets    = $this->planetRepository->findCompetitors();

        // Pour chaque saison, récupérer le classement complet
        $seasonRankings = [];
        foreach ($allSeasons as $season) {
            $map = [];
            foreach ($planets as $planet) {
                $psp = $this->pspRepository->findByPlanetAndSeason($planet, $season);
                $map[$planet->getId()] = $psp ? $psp->getTotalPoints() : 0;
            }
            arsort($map);

            $ranked = [];
            foreach ($map as $planetId => $pts) {
                foreach ($planets as $planet) {
                    if ($planet->getId() === $planetId) {
                        $ranked[] = ['planet' => $planet, 'points' => $pts];
                        break;
                    }
                }
            }
            $seasonRankings[] = ['season' => $season, 'ranking' => $ranked];
        }

        return $this->render('front/planet/archives.html.twig', [
            'seasonRankings' => $seasonRankings,
        ]);
    }

    private const ACTIVITIES_PER_PAGE = 10;

    #[Route('/{slug}', name: '_show')]
    public function show(Planet $planet, Request $request): Response
    {
        $season       = $this->seasonService->getActiveSeason();
        $astronauts   = $this->astronautRepository->findActiveByPlanet($planet);
        $seasonPoints = $season ? $this->pspRepository->findByPlanetAndSeason($planet, $season) : null;

        $page           = max(1, $request->query->getInt('page', 1));
        $totalActivities = $season ? $this->activityRepository->countByPlanetAndSeason($planet, $season) : 0;
        $totalPages      = (int) ceil($totalActivities / self::ACTIVITIES_PER_PAGE);
        $activities      = $season ? $this->activityRepository->findByPlanetAndSeasonPaginated(
            $planet, $season, $page, self::ACTIVITIES_PER_PAGE
        ) : [];

        return $this->render('front/planet/show.html.twig', [
            'planet'          => $planet,
            'astronauts'      => $astronauts,
            'season'          => $season,
            'seasonPoints'    => $seasonPoints,
            'activities'      => $activities,
            'currentPage'     => $page,
            'totalPages'      => $totalPages,
            'totalActivities' => $totalActivities,
        ]);
    }
}
