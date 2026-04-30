<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController extends AbstractController
{
    public function profile(#[CurrentUser] ?User $user): Response
    {
        if (!$user) {
            return $this->redirectToRoute('home');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}
