<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\UserSettings;
use App\Helper\GoogleClientParametersHelper;
use App\Service\Security\EncryptionService;
use Exception;
use Google\Client;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Initiate the connexion with Google SDK client.
 */
class GoogleClientService
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly GoogleClientParametersHelper $parametersHelper,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getClientForUser(UserSettings $userSettings): Client
    {
        $client = new Client();
        $accessToken = $userSettings->getGoogleDriveToken();

        try {
            $this->parametersHelper->setExtraParameters($client);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new RuntimeException('Google API parameters not found.');
        }

        // first time authorization won't have token.
        if (false === is_string($accessToken) | '' === $accessToken) {
            return $client;
        }

        /** @var string $accessToken */
        $plainToken = $this->encryptionService->decrypt($accessToken);

        if (null === $plainToken) {
            throw new RuntimeException('Token decryption failed.');
        }

        $client->setAccessToken($plainToken);

        return $client;
    }
}
