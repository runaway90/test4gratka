<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Service\LikeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    public function __construct(
        private readonly LikeService $likeService,
        private readonly LikeRepository $likeRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/photo/{id}/like', name: 'photo_like', methods: ['POST'])]
    public function like(string $id): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }

        $photo = $this->entityManager->getRepository(Photo::class)->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Photo not found');
        }

        if ($this->likeRepository->findOneByUserAndPhoto($user, $photo)) {
            $this->likeService->unlike($photo, $user);
            $this->addFlash('info', 'Photo unliked!');
        } else {
            $this->likeService->like($photo, $user);
            $this->addFlash('success', 'Photo liked!');
        }

        return $this->redirectToRoute('home');
    }
}
