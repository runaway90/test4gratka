<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Photo;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed',
    description: 'Seed the database with sample users and photos',
)]
class SeedDatabaseCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seeding database with sample data');

        $userRepository = $this->entityManager->getRepository(User::class);

        $usersData = [
            ['username' => 'nature_lover', 'email' => 'nature@example.com', 'name' => 'Emma', 'lastName' => 'Wilson', 'age' => 28, 'bio' => 'Passionate about wildlife...'],
            ['username' => 'wildlife_pro', 'email' => 'wildlife@example.com', 'name' => 'James', 'lastName' => 'Anderson', 'age' => 35, 'bio' => 'Professional wildlife photographer...'],
            ['username' => 'landscape_dreams', 'email' => 'landscape@example.com', 'name' => 'Sofia', 'lastName' => 'Martinez', 'age' => 31, 'bio' => 'Capturing the beauty...'],
            ['username' => 'animal_eyes', 'email' => 'animals@example.com', 'name' => 'Michael', 'lastName' => 'Brown', 'age' => 42, 'bio' => 'Specializing in animal photography...'],
        ];

        $userEntities = [];
        foreach ($usersData as $userData) {
            $user = $userRepository->findOneBy(['username' => $userData['username']]);
            if (!$user) {
                $user = new User();
                $user->setUsername($userData['username'])->setEmail($userData['email'])->setName($userData['name'])->setLastName($userData['lastName'])->setAge($userData['age'])->setBio($userData['bio'])->setRoles(['ROLE_USER'])->setPassword($this->passwordHasher->hashPassword($user, 'password'));
                $this->entityManager->persist($user);
                $io->text("Created user: {$userData['username']}");
            } else {
                $io->text("User {$userData['username']} already exists, skipping.");
            }
            $userEntities[$user->getUsername()] = $user;
        }
        $this->entityManager->flush();

        $photosData = [
            ['imageUrl' => 'https://picsum.photos/seed/forest1/800/600', 'location' => 'Olympic National Park, Washington', 'description' => 'Misty morning...', 'camera' => 'Canon EOS R5', 'takenAt' => '2024-03-15 07:30:00', 'username' => 'nature_lover'],
            ['imageUrl' => 'https://picsum.photos/seed/mountain1/800/600', 'location' => 'Swiss Alps', 'description' => 'Breathtaking view...', 'camera' => 'Sony A7R IV', 'takenAt' => '2024-01-22 06:15:00', 'username' => 'landscape_dreams'],
            ['imageUrl' => 'https://picsum.photos/seed/deer1/800/600', 'location' => 'Yellowstone National Park', 'description' => 'A majestic deer...', 'camera' => 'Nikon D850', 'takenAt' => '2024-05-10 17:45:00', 'username' => 'wildlife_pro'],
            ['imageUrl' => 'https://picsum.photos/seed/ocean1/800/600', 'location' => 'Big Sur, California', 'description' => 'Rugged coastline...', 'camera' => 'Fujifilm X-T4', 'takenAt' => '2024-04-08 18:20:00', 'username' => 'landscape_dreams'],
            ['imageUrl' => 'https://picsum.photos/seed/bird1/800/600', 'location' => 'Amazon Rainforest, Brazil', 'description' => 'Vibrant tropical bird...', 'camera' => 'Canon EOS R6', 'takenAt' => '2024-02-14 09:30:00', 'username' => 'animal_eyes'],
            ['imageUrl' => 'https://picsum.photos/seed/lake1/800/600', 'location' => 'Lake Louise, Canada', 'description' => 'Crystal clear mountain lake...', 'camera' => 'Sony A7 III', 'takenAt' => '2024-06-25 11:00:00', 'username' => 'nature_lover'],
            ['imageUrl' => 'https://picsum.photos/seed/fox1/800/600', 'location' => 'Scottish Highlands', 'description' => 'A curious fox...', 'camera' => 'Nikon Z7 II', 'takenAt' => '2024-07-03 05:45:00', 'username' => 'animal_eyes'],
            ['imageUrl' => 'https://picsum.photos/seed/waterfall1/800/600', 'location' => 'Iceland', 'description' => 'Powerful waterfall...', 'camera' => 'Canon EOS 5D Mark IV', 'takenAt' => '2024-08-19 14:30:00', 'username' => 'landscape_dreams'],
            ['imageUrl' => 'https://picsum.photos/seed/bear1/800/600', 'location' => 'Alaska', 'description' => 'Brown bear fishing...', 'camera' => 'Nikon D6', 'takenAt' => '2024-09-05 16:00:00', 'username' => 'wildlife_pro'],
            ['imageUrl' => 'https://picsum.photos/seed/sunset1/800/600', 'location' => 'Serengeti, Tanzania', 'description' => 'Golden hour...', 'camera' => 'Sony A1', 'takenAt' => '2024-10-12 19:15:00', 'username' => 'nature_lover'],
            ['imageUrl' => 'https://picsum.photos/seed/wolf1/800/600', 'location' => 'Canadian Rockies', 'description' => 'A lone wolf...', 'camera' => 'Canon EOS-1D X Mark III', 'takenAt' => '2024-11-20 08:30:00', 'username' => 'wildlife_pro'],
            ['imageUrl' => 'https://picsum.photos/seed/meadow1/800/600', 'location' => 'Alps, Austria', 'description' => 'Wildflower meadow...', 'camera' => 'Fujifilm GFX 100', 'takenAt' => '2024-05-18 13:20:00', 'username' => 'landscape_dreams'],
        ];

        $photoRepository = $this->entityManager->getRepository(Photo::class);
        foreach ($photosData as $photoData) {
            $photo = $photoRepository->findOneBy(['imageUrl' => $photoData['imageUrl']]);
            if (!$photo && isset($userEntities[$photoData['username']])) {
                $photo = new Photo();
                $photo->setImageUrl($photoData['imageUrl'])
                      ->setLocation($photoData['location'])
                      ->setDescription($photoData['description'])
                      ->setCamera($photoData['camera'])
                      ->setTakenAt(new \DateTimeImmutable($photoData['takenAt']))
                      ->setUser($userEntities[$photoData['username']]);
                $this->entityManager->persist($photo);
                $io->text("Created photo: {$photoData['description']}");
            } else {
                $io->text("Photo with URL {$photoData['imageUrl']} already exists, skipping.");
            }
        }
        $this->entityManager->flush();

        $io->success('Database seeding/update complete!');
        return Command::SUCCESS;
    }
}
