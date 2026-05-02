<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Like;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Service\LikeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LikeServiceTest extends TestCase
{
    private LikeService $likeService;
    private EntityManagerInterface&MockObject $entityManager;
    private LikeRepository&MockObject $likeRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->likeRepository = $this->createMock(LikeRepository::class);
        $this->likeService = new LikeService($this->entityManager, $this->likeRepository);
    }

    public function test_like_creates_like_and_increments_counter(): void
    {
        $photo = $this->createMock(Photo::class);
        $user = $this->createMock(User::class);

        $this->likeRepository->method('findOneByUserAndPhoto')->willReturn(null);
        $photo->expects($this->once())->method('incrementLikeCounter');
        $this->entityManager->expects($this->exactly(2))->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->likeService->like($photo, $user);
    }

    public function test_like_does_nothing_when_already_liked(): void
    {
        $photo = $this->createMock(Photo::class);
        $user = $this->createMock(User::class);

        $this->likeRepository->method('findOneByUserAndPhoto')->willReturn($this->createMock(Like::class));
        $photo->expects($this->never())->method('incrementLikeCounter');
        $this->entityManager->expects($this->never())->method('flush');

        $this->likeService->like($photo, $user);
    }

    public function test_unlike_removes_like_and_decrements_counter(): void
    {
        $photo = $this->createMock(Photo::class);
        $user = $this->createMock(User::class);
        $like = $this->createMock(Like::class);

        $this->likeRepository->method('findOneByUserAndPhoto')->willReturn($like);
        $photo->expects($this->once())->method('decrementLikeCounter');
        $this->entityManager->expects($this->once())->method('remove')->with($like);
        $this->entityManager->expects($this->once())->method('flush');

        $this->likeService->unlike($photo, $user);
    }

    public function test_unlike_does_nothing_when_like_not_exists(): void
    {
        $photo = $this->createMock(Photo::class);
        $user = $this->createMock(User::class);

        $this->likeRepository->method('findOneByUserAndPhoto')->willReturn(null);
        $photo->expects($this->never())->method('decrementLikeCounter');
        $this->entityManager->expects($this->never())->method('flush');

        $this->likeService->unlike($photo, $user);
    }
}
