<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\AuthTokenRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthService
{
    public function __construct(
        private AuthTokenRepository $authTokenRepository,
        private UserRepository $userRepository,
        private RequestStack $requestStack
    ) {
    }

    public function login(string $username, string $token): void
    {
        $authToken = $this->authTokenRepository->findOneByToken($token);
        if (!$authToken) {
            throw new AccessDeniedException('Invalid token');
        }

        $user = $this->userRepository->findOneByUsername($username);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        $session = $this->requestStack->getSession();
        $session->set('user_id', $user->getId());
        $session->set('username', $user->getUsername());
    }

    public function logout(): void
    {
        $this->requestStack->getSession()->clear();
    }
}
