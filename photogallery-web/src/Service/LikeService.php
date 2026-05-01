<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Like;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;

class LikeService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LikeRepository $likeRepository
    ) {
    }

    public function like(Photo $photo, User $user): void
    {
        if ($this->likeRepository->findOneByUserAndPhoto($user, $photo)) {
            return;
        }

        $like = new Like();
        $like->setUser($user);
        $like->setPhoto($photo);

        $photo->incrementLikeCounter();

        $this->entityManager->persist($like);
        $this->entityManager->persist($photo);
        $this->entityManager->flush();
    }

    public function unlike(Photo $photo, User $user): void
    {
        $like = $this->likeRepository->findOneByUserAndPhoto($user, $photo);

        if (!$like) {
            return;
        }

        $photo->decrementLikeCounter();

        $this->entityManager->remove($like);
        $this->entityManager->persist($photo);
        $this->entityManager->flush();
    }
}
