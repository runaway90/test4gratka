<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Service\CookieService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        CookieService $cookieService
    ): Response {
        if ($request->isMethod('GET')) {
            return $this->render('login/index.html.twig');
        }

        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if (!$username || !$password) {
            $this->addFlash('error', 'Missing credentials');
            return $this->redirectToRoute('login');
        }

        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            $this->addFlash('error', 'Invalid credentials');
            return $this->redirectToRoute('login');
        }

        $token = $jwtManager->create($user);

        $response = $this->redirectToRoute('home');
        $response->headers->setCookie($cookieService->createAuthCookie($token));
        $this->addFlash('success', 'Welcome back, ' . $user->getUsername() . '!');

        return $response;
    }

    public function logout(AuthService $authService): Response
    {
        return $authService->logout();
    }
}
