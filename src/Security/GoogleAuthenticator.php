<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Astronaut;
use App\Repository\AstronautRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    private const ALLOWED_DOMAIN = 'eleven-labs.com';

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RouterInterface $router,
        private readonly AstronautRepository $astronautRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();

                // 1. Vérification du domaine @eleven-labs.com
                $domain = substr(strrchr($email, '@'), 1);
                if ($domain !== self::ALLOWED_DOMAIN) {
                    throw new CustomUserMessageAuthenticationException(
                        'Accès réservé aux membres d\'Eleven Labs (@' . self::ALLOWED_DOMAIN . ').'
                    );
                }

                // 2. Recherche du compte — PAS de création automatique
                $astronaut = $this->astronautRepository->findByEmailOrGoogleId($email);

                if ($astronaut === null) {
                    throw new CustomUserMessageAuthenticationException(
                        'Votre compte n\'a pas encore été activé. Contactez un administrateur.'
                    );
                }

                if (!$astronaut->isActive()) {
                    throw new CustomUserMessageAuthenticationException(
                        'Votre compte est désactivé. Contactez un administrateur.'
                    );
                }

                // 3. Stocker le googleId et la photo si c'est la première connexion
                $needsFlush = false;

                if ($astronaut->getGoogleId() === null) {
                    $astronaut->setGoogleId($googleUser->getId());
                    $needsFlush = true;
                }

                if ($astronaut->getPhoto() === null && $googleUser->getAvatar() !== null) {
                    try {
                        $path = $this->fileUploadService->uploadFromUrl($googleUser->getAvatar(), 'astronauts');
                        $astronaut->setPhoto($path);
                        $needsFlush = true;
                    } catch (\Throwable) {
                        // Echec silencieux : la photo n'est pas critique
                    }
                }

                if ($needsFlush) {
                    $this->entityManager->flush();
                }

                return $astronaut;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('front_planets'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set('auth_error', strtr($exception->getMessageKey(), $exception->getMessageData()));

        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}
