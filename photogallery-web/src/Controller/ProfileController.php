<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\User;
use App\Form\ApiTokenFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%app.phoenix_api_url%')]
        private readonly string $phoenixApiUrl
    ) {
    }

    public function profile(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(ApiTokenFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'API Tokens updated successfully!');

            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'tokenForm' => $form->createView(),
        ]);
    }

    public function importPhotos(#[CurrentUser] User $user): Response
    {
        $token = $user->getPhoenixApiToken(); // Use phoenixApiToken

        if (!$token) {
            $this->addFlash('warning', 'Please save a PhoenixAPI Access Token before importing photos.');
            return $this->redirectToRoute('profile');
        }

        try {
            // Corrected PhoenixAPI URL
            $phoenixApiUrl = $this->phoenixApiUrl;

            $response = $this->httpClient->request('GET', $phoenixApiUrl, [
                'headers' => [
                    'access-token' => $token,
                    'Accept' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->addFlash('error', 'Invalid PhoenixAPI Access Token or PhoenixAPI error. Status: ' . $response->getStatusCode());
                return $this->redirectToRoute('profile');
            }

            $data = $response->toArray();
            $importedCount = 0;
            $photoRepository = $this->entityManager->getRepository(Photo::class);

            if (isset($data['photos']) && is_array($data['photos'])) {
                foreach ($data['photos'] as $photoData) {
                    // Prevent duplicates based on image URL
                    if (isset($photoData['photo_url']) && !$photoRepository->findOneBy(['imageUrl' => $photoData['photo_url']])) {
                        $photo = new Photo();
                        $photo->setImageUrl($photoData['photo_url']);
                        $photo->setUser($user);
                        
                        $this->entityManager->persist($photo);
                        $importedCount++;
                    }
                }
            } else {
                $this->addFlash('info', 'PhoenixAPI returned no photos or an unexpected data structure.');
                return $this->redirectToRoute('profile');
            }


            if ($importedCount > 0) {
                $this->entityManager->flush();
                $this->addFlash('success', sprintf('Successfully imported %d new photos from PhoenixAPI!', $importedCount));
            } else {
                $this->addFlash('info', 'No new photos to import from PhoenixAPI.');
            }

        } catch (\Exception $e) {
            // Log the exception here if you have a logger
            // $this->logger->error('Error importing photos from PhoenixAPI: ' . $e->getMessage());
            $this->addFlash('error', 'An unexpected error occurred during PhoenixAPI import: ' . $e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }
}
