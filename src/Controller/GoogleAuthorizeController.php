<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\Google\GoogleDriveService;
use App\Service\Security\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Google\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Random\RandomException;
use SodiumException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GoogleAuthorizeController extends AbstractController
{
    #[Route('/google/authorize-request', name: 'app_google_authorize_request')]
    public function index(GoogleDriveService $googleService): RedirectResponse
    {
        try {
            $client = $googleService->getClient();
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $this->addFlash('error', 'An error occurred while trying to authorize Google Drive access.');

            return $this->redirectToRoute('app_settings');
        }

        return $this->redirect($client->createAuthUrl());
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     */
    #[Route('/google/authorize-response', name: 'app_google_authorize_response')]
    public function response(
        Request $request,
        TokenService $tokenService,
        EntityManagerInterface $entityManager
    ): RedirectResponse
    {
        $code = $request->query->get('code');

        if (null === $code || '' === $code) {
            $this->addFlash('error', 'No code provided in google response.');

            return $this->redirectToRoute('app_settings');
        }

        return $this->storeTokenForUser($code, $tokenService, $entityManager);
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     */
    private function storeTokenForUser(
        string $code,
        TokenService $tokenService,
        EntityManagerInterface $entityManager
    ): RedirectResponse
    {
        $user = $this->getUser();

        if (false === $user instanceof User) {
            $this->addFlash('error', 'You must be logged in to authorize Google Drive access.');

            return $this->redirectToRoute('app_settings');
        }

        $user->getSettings()->setGoogleDriveToken($tokenService->encrypt($code));

        $this->addFlash('success', 'Google Drive access has been given.');

        $entityManager->flush();

        return $this->redirectToRoute('app_settings');
    }
}
