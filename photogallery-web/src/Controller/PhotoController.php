<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Photo;
use App\Likes\LikeRepository;
use App\Likes\LikeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PhotoController extends AbstractController
{
    public function __construct(
        private LikeRepository $likeRepository,
        private LikeService $likeService
    ) {
    }

    public function like(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to like photos.');
            return $this->redirectToRoute('login');
        }

        /** @var Photo|null $photo */
        $photo = $em->getRepository(Photo::class)->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Photo not found');
        }

        $this->likeRepository->setUser($user);

        if ($this->likeRepository->hasUserLikedPhoto($photo)) {
            $this->likeRepository->unlikePhoto($photo);
            $this->addFlash('info', 'Photo unliked!');
        } else {
            $this->likeService->execute($photo);
            $this->addFlash('success', 'Photo liked!');
        }

        return $this->redirectToRoute('home');
    }
}
