<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\UserSettings;
use App\Helper\GoogleClientHelper;
use App\Service\Google\OAuth\GoogleOAuthTokenService;
use App\Service\Security\EncryptionService;
use Google\Client;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Random\RandomException;
use RuntimeException;
use SodiumException;

/**
 * Initiate the connexion with Google SDK client.
 */
class GoogleClientService
{
    public function __construct(
        private readonly EncryptionService       $encryptionService,
        private readonly GoogleClientHelper      $clientHelper,
        private readonly GoogleOAuthTokenService $tokenService
    ) {
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws RandomException
     * @throws ContainerExceptionInterface
     * @throws SodiumException
     */
    public function getClientForUser(UserSettings $userSettings): Client
    {
        $client = $this->clientHelper->create();
        $accessToken = $this->tokenService
            ->getAccessToken($userSettings, $client)
            ->getToken();

        // first time authorization won't have token.
        if (false === is_string($accessToken) | '' === $accessToken) {
            return $client;
        }

        $plainToken = $this->encryptionService->decrypt($accessToken);

        if (null === $plainToken) {
            throw new RuntimeException('Token decryption failed.');
        }

        $client->setAccessToken($plainToken);

        return $client;
    }
}
