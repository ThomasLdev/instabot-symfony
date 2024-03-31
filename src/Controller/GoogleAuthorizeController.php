<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\Google\GoogleDriveClientService;
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
    public function index(GoogleDriveClientService $googleService): RedirectResponse
    {
        $user = $this->getUser();

        if (false === $user instanceof User) {
            $this->addFlash('error', 'You must be logged in to authorize Google Drive access.');

            return $this->redirectToRoute('app_index');
        }

        try {
            $client = $googleService->getClientForUser($user->getSettings());
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
        Request                $request,
        EncryptionService      $tokenService,
        EntityManagerInterface $entityManager
    ): RedirectResponse
    {
        $authCode = $request->query->get('code');

        if (null === $authCode || '' === $authCode) {
            $this->addFlash('error', 'No code provided in google response.');

            return $this->redirectToRoute('app_settings');
        }

        return $this->storeAuthCodeForUser($authCode, $tokenService, $entityManager);
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     */
    private function storeAuthCodeForUser(
        string                 $authCode,
        EncryptionService      $tokenService,
        EntityManagerInterface $entityManager
    ): RedirectResponse
    {
        $user = $this->getUser();

        if (false === $user instanceof User) {
            $this->addFlash('error', 'You must be logged in to authorize Google Drive access.');

            return $this->redirectToRoute('app_index');
        }

        $user->getSettings()->setGoogleDriveAuthCode($tokenService->encrypt($authCode));

        $this->addFlash('success', 'Google Drive access has been granted.');

        $entityManager->flush();

        return $this->redirectToRoute('app_settings');
    }
}
