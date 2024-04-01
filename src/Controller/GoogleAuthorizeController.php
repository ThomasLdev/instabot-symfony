<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserSettings;
use App\Service\Google\GoogleClientService;
use App\Service\Security\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
    public function index(GoogleClientService $clientService): RedirectResponse
    {
        $user = $this->getUser();

        if (false === $user instanceof User) {
            $this->addFlash('error', 'You must be logged in to authorize Google Drive access.');

            return $this->redirectToRoute('app_index');
        }

        /** @var UserSettings $settings */
        $settings = $user->getSettings();

        try {
            $client = $clientService->getClientForUser($settings);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface | Exception $e) {
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
        EncryptionService $tokenService,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $authCode = $request->query->get('code');

        if (false === is_string($authCode)) {
            $this->addFlash('error', 'No code provided in google response.');

            return $this->redirectToRoute('app_settings');
        }

        return $this->storeAuthCodeForUser($authCode, $tokenService, $entityManager);
    }

    #[Route('/google/revoke-access', name: 'app_google_revoke_access')]
    public function revokeAuthCodeForUser(EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();

        if (false === $user instanceof User) {
            $this->addFlash('error', 'You must be logged in to revoke Google Drive access.');

            return $this->redirectToRoute('app_index');
        }

        /** @var UserSettings $settings */
        $settings = $user->getSettings();

        $settings->setGoogleDriveAuthCode(null);

        $this->addFlash('success', 'Google Drive access has been revoked.');

        $entityManager->flush();

        return $this->redirectToRoute('app_settings');
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     */
    private function storeAuthCodeForUser(
        string $authCode,
        EncryptionService $tokenService,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $user = $this->getUser();

        if (false === $user instanceof User) {
            $this->addFlash('error', 'You must be logged in to authorize Google Drive access.');

            return $this->redirectToRoute('app_index');
        }

        /** @var UserSettings $settings */
        $settings = $user->getSettings();

        $settings->setGoogleDriveAuthCode($tokenService->encrypt($authCode));

        $this->addFlash('success', 'Google Drive access has been granted.');

        $entityManager->flush();

        return $this->redirectToRoute('app_settings');
    }
}
