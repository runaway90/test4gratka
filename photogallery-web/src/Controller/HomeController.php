<?php

declare(strict_types=1);

namespace App\Controller;

use App\Likes\LikeRepository;
use App\Repository\PhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    public function __construct(
        private PhotoRepository $photoRepository,
        private LikeRepository $likeRepository
    ) {
    }

    public function index(): Response
    {
        $photos = $this->photoRepository->findAllWithUsers();
        $currentUser = $this->getUser();
        $userLikes = [];

        if ($currentUser) {
            $this->likeRepository->setUser($currentUser);
            foreach ($photos as $photo) {
                $userLikes[$photo->getId()] = $this->likeRepository->hasUserLikedPhoto($photo);
            }
        }

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'currentUser' => $currentUser,
            'userLikes' => $userLikes,
        ]);
    }
}
