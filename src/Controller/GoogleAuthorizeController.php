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

        $settings = $this->getUserSettings();

        if (null === $settings) {
            return $this->flashOnRedirect(
                'error',
                'errors.controller.drive.no_settings',
                self::SETTINGS_ROUTE
            );
        }

        try {
            $client = $clientService->getClientForUser($settings);
        } catch (Exception $e) {
            return $this->flashOnRedirect(
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

        $settings = $this->getUserSettings();

        if (null === $settings) {
            return $this->flashOnRedirect(
                'error',
                'errors.controller.drive.no_settings',
                self::SETTINGS_ROUTE
            );
        }

        $authCode = $request->query->get('code');

        if (false === is_string($authCode)) {
            return $this->flashOnRedirect(
                'error',
                'errors.controller.google.no_code',
                BaseController::SETTINGS_ROUTE
            );
        }

        /** @var string $authCode */
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

        $settings = $this->getUserSettings();

        if (null === $settings) {
            return $this->flashOnRedirect(
                'error',
                'errors.controller.drive.no_settings',
                self::SETTINGS_ROUTE
            );
        }

        $settings
            ->setGoogleDriveAuthCode(null)
            ->setGoogleDriveToken(null)
            ->setGoogleDriveTokenIssueTime(null)
            ->setGoogleDriveTokenExpiry(null);

        $entityManager->flush();

        return $this->flashOnRedirect(
            'success',
            'form.update.token.revoke',
            BaseController::SETTINGS_ROUTE
        );
    }
}
