<?php

declare(strict_types=1);

namespace App\Controller\Dev;

use App\Repository\AstronautRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Route de login utilisée uniquement par les tests E2E Playwright (env dev).
 * NE PAS déployer en production.
 */
class TestLoginController extends AbstractController
{
    public function __construct(
        private readonly AstronautRepository $astronautRepository,
    ) {}

    #[Route('/test/login', name: 'test_login', condition: "'%kernel.environment%' === 'dev'")]
    public function login(Request $request): Response
    {
        $email = $request->query->getString('email');

        if ($email === '') {
            return new Response('Paramètre email manquant', 400);
        }

        $astronaut = $this->astronautRepository->findOneBy(['email' => $email]);

        if ($astronaut === null) {
            return new Response("Astronaute introuvable : $email", 404);
        }

        $token = new UsernamePasswordToken($astronaut, 'main', $astronaut->getRoles());
        $request->getSession()->set('_security_main', serialize($token));

        return $this->redirectToRoute('front_planets');
    }
}
