<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack
    ) {
    }

    public function logout(): RedirectResponse
    {
        $response = new RedirectResponse($this->urlGenerator->generate('home'));
        $response->headers->clearCookie('BEARER');

        $this->requestStack->getSession()->getFlashBag()->add('info', 'You have been logged out successfully.');

        return $response;
    }
}
