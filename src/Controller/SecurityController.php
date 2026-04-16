<?php

declare(strict_types=1);

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('front_planets');
        }

        $error = $request->getSession()->get('auth_error');
        $request->getSession()->remove('auth_error');

        return $this->render('security/login.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/connect/google', name: 'connect_google')]
    public function connectGoogle(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['email', 'profile'], []);
    }

    #[Route('/auth/callback', name: 'connect_google_check')]
    public function connectGoogleCheck(): Response
    {
        // Géré par GoogleAuthenticator — ne devrait jamais être atteint
        throw new \LogicException('Ce contrôleur ne devrait pas être atteint directement.');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Géré par Symfony Security — ne devrait jamais être atteint
        throw new \LogicException('Ce contrôleur ne devrait pas être atteint directement.');
    }
}
