<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/auth/{username}/{token}', name: 'auth_login')]
    public function login(string $username, string $token, Connection $connection, Request $request): Response
    {
        // T1-01. SQL-injection protected
        $sql = "SELECT * FROM auth_tokens WHERE token = :token";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('token', $token);
        $result = $stmt->executeQuery();
        $tokenData = $result->fetchAssociative();

        if (!$tokenData) {
            return new Response('Invalid token', 401);
        }

        // T1-01. SQL-injection protected
        $userSql = "SELECT * FROM users WHERE username = :username";
        $userStmt = $connection->prepare($userSql);
        $userStmt->bindValue('username', $username);
        $userResult = $userStmt->executeQuery();
        $userData = $userResult->fetchAssociative();

        if (!$userData) {
            return new Response('User not found', 404);
        }

        $session = $request->getSession();
        $session->set('user_id', $userData['id']);
        $session->set('username', $username);

        $this->addFlash('success', 'Welcome back, ' . $username . '!');

        return $this->redirectToRoute('home');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->clear();

        $this->addFlash('info', 'You have been logged out successfully.');

        return $this->redirectToRoute('home');
    }
}
