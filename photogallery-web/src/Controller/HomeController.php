<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Likes\LikeRepository;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    private PhotoRepository $photoRepository;
    private LikeRepository $likeRepository;

    public function __construct(PhotoRepository $photoRepository, LikeRepository $likeRepository)
    {
        $this->photoRepository = $photoRepository;
        $this->likeRepository = $likeRepository;
    }

    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $photos = $this->photoRepository->findAllWithUsers();

        $session = $request->getSession();
        $userId = $session->get('user_id');
        $currentUser = null;
        $userLikes = [];

        if ($userId) {
            $currentUser = $em->getRepository(User::class)->find($userId);

            if ($currentUser) {
                foreach ($photos as $photo) {
                    $this->likeRepository->setUser($currentUser);
                    $userLikes[$photo->getId()] = $this->likeRepository->hasUserLikedPhoto($photo);
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'currentUser' => $currentUser,
            'userLikes' => $userLikes,
        ]);
    }
}
