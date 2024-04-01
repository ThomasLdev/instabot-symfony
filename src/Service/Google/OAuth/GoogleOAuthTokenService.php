<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google\OAuth;

use App\Entity\UserSettings;
use App\Helper\TokenHelper;
use App\Model\GoogleClientResponse;
use App\Service\Security\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Random\RandomException;
use SodiumException;

class GoogleOAuthTokenService
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenHelper $tokenHelper,
        private readonly GoogleOAuthResponseService $OAuthResponse
    ) {
    }

    /**
     * @throws SodiumException
     * @throws RandomException
     */
    public function getToken(
        ?string $accessToken,
        string $authCode,
        Client $client,
        UserSettings $userSettings
    ): GoogleClientResponse {
        if ((null !== $accessToken) && $this->tokenHelper->isValid($userSettings)) {
            return $this->OAuthResponse->handleResponse([
                'access_token' => $accessToken,
            ]);
        }

        $data = $client->fetchAccessTokenWithAuthCode($this->encryptionService->decrypt($authCode));
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
            ->setGoogleDriveToken($this->encryptionService->encrypt($data['access_token']))
            ->setGoogleDriveAuthCode($this->encryptionService->encrypt($data['refresh_token']))
            ->setGoogleDriveTokenExpiry($data['expires_in'])
            ->setGoogleDriveTokenIssueTime(time());

        $this->entityManager->flush();
    }
}
