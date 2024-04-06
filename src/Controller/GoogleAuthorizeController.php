<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\Google\GoogleClientService;
use App\Service\Google\OAuth\GoogleOAuthTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Random\RandomException;
use SodiumException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GoogleAuthorizeController extends BaseController
{
    #[Route('/google/authorize-request', name: 'app_google_authorize_request')]
    public function index(GoogleClientService $clientService): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $client = null;
        $settings = $this->getAppUserSettings($this->getAppUser());

        try {
            $client = $clientService->getClientForUser($settings);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface | Exception $e) {
            $this->flashOnRedirect(
                'error',
                'errors.controller.google.authorization_failed',
                BaseController::SETTINGS_ROUTE
            );
        }

        if (null === $client) {
            $this->flashOnRedirect(
                'error',
                'errors.controller.google.authorization_failed',
                BaseController::SETTINGS_ROUTE
            );
        }

        return $this->redirect($client->createAuthUrl());
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     */
    #[Route('/google/authorize-response', name: 'app_google_authorize_response')]
    public function response(Request $request, GoogleOAuthTokenService $tokenService): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $settings = $this->getAppUserSettings($this->getAppUser());
        $authCode = $request->query->get('code');

        if (false === is_string($authCode)) {
            $this->flashOnRedirect(
                'error',
                'errors.controller.google.no_code',
                BaseController::SETTINGS_ROUTE
            );
        }

        $response = $tokenService->storeAuthCodeForUser($settings, $authCode);

        if (false === $response->getSuccess()) {
            return $this->flashOnRedirect(
                'error',
                'errors.controller.google.authorization_failed',
                BaseController::INDEX_ROUTE
            );
        }

        return $this->flashOnRedirect(
            'success',
            'errors.controller.google.authorization_success',
            BaseController::INDEX_ROUTE
        );
    }

    #[Route('/google/revoke-access', name: 'app_google_revoke_access')]
    public function revokeAuthCodeForUser(EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $settings = $this->getAppUserSettings($this->getAppUser());
        $settings->setGoogleDriveAuthCode(null);
        $settings->setGoogleDriveToken(null);

        $entityManager->flush();

        return $this->flashOnRedirect(
            'success',
            'form.update.token.revoke',
            BaseController::SETTINGS_ROUTE
        );
    }
}
