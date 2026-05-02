<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Photo;
use App\Entity\User;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PhotoRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private PhotoRepository $photoRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->photoRepository = $this->entityManager->getRepository(Photo::class);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    public function test_find_by_filters_returns_all_when_no_filters(): void
    {
        $user = $this->createUser('filter_test_user');
        $this->createPhoto($user, 'https://example.com/1.jpg', 'Paris', 'Canon', 'Sunset');
        $this->createPhoto($user, 'https://example.com/2.jpg', 'Berlin', 'Nikon', 'Sunrise');
        $this->entityManager->flush();

        $results = $this->photoRepository->findByFilters([]);

        $urls = array_map(fn(Photo $p) => $p->getImageUrl(), $results);
        $this->assertContains('https://example.com/1.jpg', $urls);
        $this->assertContains('https://example.com/2.jpg', $urls);
    }

    public function test_find_by_filters_filters_by_location(): void
    {
        $user = $this->createUser('location_test_user');
        $this->createPhoto($user, 'https://example.com/paris.jpg', 'Paris', 'Canon', 'City lights');
        $this->createPhoto($user, 'https://example.com/berlin.jpg', 'Berlin', 'Nikon', 'Brandenburg Gate');
        $this->entityManager->flush();

        $results = $this->photoRepository->findByFilters(['location' => 'Paris']);

        $urls = array_map(fn(Photo $p) => $p->getImageUrl(), $results);
        $this->assertContains('https://example.com/paris.jpg', $urls);
        $this->assertNotContains('https://example.com/berlin.jpg', $urls);
    }

    public function test_find_by_filters_filters_by_camera(): void
    {
        $user = $this->createUser('camera_test_user');
        $this->createPhoto($user, 'https://example.com/canon.jpg', 'Rome', 'Canon EOS R5', 'Colosseum');
        $this->createPhoto($user, 'https://example.com/sony.jpg', 'Rome', 'Sony A7III', 'Colosseum');
        $this->entityManager->flush();

        $results = $this->photoRepository->findByFilters(['camera' => 'Canon']);

        $urls = array_map(fn(Photo $p) => $p->getImageUrl(), $results);
        $this->assertContains('https://example.com/canon.jpg', $urls);
        $this->assertNotContains('https://example.com/sony.jpg', $urls);
    }

    public function test_find_by_filters_filters_by_username(): void
    {
        $user1 = $this->createUser('alice_repo_test');
        $user2 = $this->createUser('bob_repo_test');
        $this->createPhoto($user1, 'https://example.com/alice.jpg', 'Tokyo', 'Canon', 'Alice photo');
        $this->createPhoto($user2, 'https://example.com/bob.jpg', 'Tokyo', 'Canon', 'Bob photo');
        $this->entityManager->flush();

        $results = $this->photoRepository->findByFilters(['username' => 'alice']);

        $urls = array_map(fn(Photo $p) => $p->getImageUrl(), $results);
        $this->assertContains('https://example.com/alice.jpg', $urls);
        $this->assertNotContains('https://example.com/bob.jpg', $urls);
    }

    public function test_find_by_filters_returns_empty_when_no_match(): void
    {
        $user = $this->createUser('nomatch_test_user');
        $this->createPhoto($user, 'https://example.com/x.jpg', 'Madrid', 'Fuji', 'Flamenco');
        $this->entityManager->flush();

        $results = $this->photoRepository->findByFilters(['location' => 'nonexistent_xyz_location']);

        $this->assertEmpty($results);
    }

    private function createUser(string $username): User
    {
        $user = new User();
        $user->setUsername($username)
            ->setEmail($username . '@test.com')
            ->setPassword('hashed_password')
            ->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);

        return $user;
    }

    private function createPhoto(User $user, string $url, string $location, string $camera, string $description): Photo
    {
        $photo = new Photo();
        $photo->setImageUrl($url)
            ->setLocation($location)
            ->setCamera($camera)
            ->setDescription($description)
            ->setUser($user);

        $this->entityManager->persist($photo);

        return $photo;
    }
}
