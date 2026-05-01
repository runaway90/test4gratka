<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\LikeRepository;
use App\Repository\PhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly PhotoRepository $photoRepository,
        private readonly LikeRepository $likeRepository
    ) {
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $photos = $this->photoRepository->findAll();
        $currentUser = $this->getUser();
        $userLikes = [];

        if ($currentUser instanceof User) {
            foreach ($photos as $photo) {
                $userLikes[$photo->getId()->toRfc4122()] = $this->likeRepository->findOneByUserAndPhoto($currentUser, $photo) !== null;
            }
        }

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'userLikes' => $userLikes,
        ]);
    }
}
