<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthController extends AbstractController
{
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
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
            throw new AccessDeniedException('Invalid credentials');
        }

        $token = $jwtManager->create($user);

        $response = $this->redirectToRoute('home');
        $response->headers->setCookie(
            new Cookie(
                'BEARER', // The name of the cookie
                $token,   // The value of the cookie
                time() + 3600, // The expiration date
                '/',      // The path on the server where the cookie will be available
                null,     // The domain that the cookie is available to
                true,     // Indicates that the cookie should only be transmitted over a secure HTTPS connection
                true,     // Indicates that the cookie will be made accessible only through the HTTP protocol
                false,
                'lax'
            )
        );
        $this->addFlash('success', 'Welcome back, ' . $user->getUsername() . '!');

        return $response;
    }

    public function logout(): Response
    {
        $response = $this->redirectToRoute('home');
        $response->headers->clearCookie('BEARER');
        $this->addFlash('info', 'You have been logged out successfully.');

        return $response;
    }
}
