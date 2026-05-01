<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Form\ApiTokenFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function profile(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(ApiTokenFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'API Token updated successfully!');

            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'tokenForm' => $form->createView(),
        ]);
    }

    public function importPhotos(#[CurrentUser] User $user): Response
    {
        $token = $user->getApiToken();

        if (!$token) {
            $this->addFlash('warning', 'Please save an API token before importing.');
            return $this->redirectToRoute('profile');
        }

        try {
            $response = $this->httpClient->request('GET', 'http://photogallery-api-api:4000/api/photos', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->addFlash('error', 'Invalid API token or API error.');
                return $this->redirectToRoute('profile');
            }

            $data = $response->toArray();
            $importedCount = 0;
            $photoRepository = $this->entityManager->getRepository(Photo::class);

            foreach ($data['photos'] as $photoData) {
                // Prevent duplicates
                if (!$photoRepository->findOneBy(['imageUrl' => $photoData['photo_url']])) {
                    $photo = new Photo();
                    $photo->setImageUrl($photoData['photo_url']);
                    $photo->setUser($user);
                    
                    $this->entityManager->persist($photo);
                    $importedCount++;
                }
            }

            if ($importedCount > 0) {
                $this->entityManager->flush();
                $this->addFlash('success', sprintf('Successfully imported %d new photos!', $importedCount));
            } else {
                $this->addFlash('info', 'No new photos to import.');
            }

        } catch (\Exception $e) {
            // Log the exception here if you have a logger
            $this->addFlash('error', 'An unexpected error occurred during import.');
        }

        return $this->redirectToRoute('profile');
    }
}
