<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthController extends AbstractController
{
    public function login(string $username, string $token, AuthService $authService): Response
    {
        try {
            $authService->login($username, $token);
            $this->addFlash('success', 'Welcome back, ' . $username . '!');
        } catch (AccessDeniedException $e) {
            return new Response($e->getMessage(), 401);
        } catch (NotFoundHttpException $e) {
            return new Response($e->getMessage(), 404);
        }

        return $this->redirectToRoute('home');
    }

    public function logout(AuthService $authService): Response
    {
        $authService->logout();
        $this->addFlash('info', 'You have been logged out successfully.');

        return $this->redirectToRoute('home');
    }
}
