<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google\OAuth;

use App\Entity\UserSettings;
use App\Helper\GoogleClientHelper;
use App\Helper\TokenHelper;
use App\Model\GoogleClientResponse;
use App\Service\Google\GoogleResponseInterface;
use App\Service\Security\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Google\Client;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Random\RandomException;
use SodiumException;

/**
 * Handle the OAuth tokens for Google Drive.
 */
class GoogleOAuthTokenService
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenHelper $tokenHelper,
        private readonly GoogleOAuthResponseService $OAuthResponse,
        private readonly GoogleClientHelper $clientHelper
    ) {
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     *
     * Store the first auth code for the user.
     * Also get the access token and refresh token.
     */
    public function storeAuthCodeForUser(UserSettings $userSettings, string $authCode): GoogleClientResponse
    {
        $userSettings->setGoogleDriveAuthCode($this->encryptionService->encrypt($authCode));

        try {
            $authResponse = $this->getAccessToken($userSettings);
        } catch (RandomException | SodiumException | Exception $e) {
            return $this->OAuthResponse->handleResponse([]);
        }

        return $authResponse;
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     * @throws Exception
     */
    public function getAccessToken(UserSettings $userSettings, ?Client $client = null): GoogleClientResponse
    {
        $token = $userSettings->getGoogleDriveToken();

        if (null !== $token && true === $this->tokenHelper->isValid($userSettings)) {
            return $this->OAuthResponse->handleResponse([
                GoogleResponseInterface::ACCESS_TOKEN_KEY => $token,
            ]);
        }

        if (null === $client) {
            try {
                $client = $this->clientHelper->create($userSettings);
            } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
                return $this->OAuthResponse->handleResponse([
                    GoogleResponseInterface::ERROR_KEY => 'errors.drive.bad_request',
                ]);
            }
        }

        $authCode = $userSettings->getGoogleDriveAuthCode();

        if (false === is_string($authCode)) {
            return $this->OAuthResponse->handleResponse([
                GoogleResponseInterface::ERROR_KEY => 'errors.drive.bad_request',
            ]);
        }

        $plainAuthCode = $this->encryptionService->decrypt($authCode);

        if (false === is_string($plainAuthCode)) {
            return $this->OAuthResponse->handleResponse([
                GoogleResponseInterface::ERROR_KEY => 'errors.drive.bad_request',
            ]);
        }

        $data = $client->fetchAccessTokenWithAuthCode($plainAuthCode);
        $response = $this->OAuthResponse->handleResponse($data);

        if (false === $response->getSuccess()) {
            return $response;
        }

        $this->refreshUserTokens($userSettings, $data);

        return $response;
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     */
    private function refreshUserTokens(UserSettings $userSettings, array $data): void
    {
        $userSettings
            ->setGoogleDriveToken($this->encryptionService->encrypt($data[GoogleResponseInterface::ACCESS_TOKEN_KEY]))
            ->setGoogleDriveAuthCode($this->encryptionService->encrypt($data['refresh_token']))
            ->setGoogleDriveTokenExpiry($data['expires_in'])
            ->setGoogleDriveTokenIssueTime(time());

        $this->entityManager->flush();
    }
}
