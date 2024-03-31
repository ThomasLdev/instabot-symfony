<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\UserSettings;
use App\Service\Security\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Random\RandomException;

class GoogleOAuthTokenService
{
    public function __construct(
        private readonly EncryptionService      $encryptionService,
        private readonly EntityManagerInterface $entityManager
    ) { }

    /**
     * @throws \JsonException
     * @throws \SodiumException
     * @throws RandomException
     */
    public function getToken(
        ?string $accessToken,
        string $authCode,
        Client $client,
        UserSettings $userSettings
    ): string
    {
        if ((null !== $accessToken) && $this->isTokenValid($userSettings)) {
            return $this->encryptionService->decrypt($accessToken);
        }

        $data = $client->fetchAccessTokenWithAuthCode($this->encryptionService->decrypt($authCode));

        if (array_key_exists('error', $data)) {
            throw new \RuntimeException('Failed to get access token: ' . $data['error']);
        }

        if (false === array_key_exists('access_token', $data)) {
            throw new \RuntimeException(
                'Failed to get access token from: ' .
                json_encode($data, JSON_THROW_ON_ERROR)
            );
        }

        $this->refreshUserTokens($userSettings, $data);

        return $data['access_token'];
    }

    /**
     * @throws RandomException
     * @throws \SodiumException
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

    private function isTokenValid(UserSettings $userSettings): bool
    {
        $expiry = $userSettings->getGoogleDriveTokenExpiry();

        if (null === $expiry) {
            return false;
        }

        $tokenIssueTime = $userSettings->getGoogleDriveTokenIssueTime();

        if (null === $tokenIssueTime) {
            return false;
        }

        $expiryTime = $tokenIssueTime + $expiry;
        $currentTime = time();

        return $currentTime < $expiryTime;
    }
}
