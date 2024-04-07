<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\UserSettings;
use App\Service\Security\EncryptionService;
use Exception;
use Google\Client;
use Google\Service\Drive;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * Initiate the connexion with Google SDK client.
 */
class GoogleClientService
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly Router            $router,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getClientForUser(UserSettings $userSettings): Client
    {
        $client = new Client();
        $accessToken = $userSettings->getGoogleDriveToken();

        $this->setClientExtraData($client);

        // first time authorization won't have token.
        if (null === $accessToken | '' === $accessToken) {
            return $client;
        }

        $client->setAccessToken($this->encryptionService->decrypt($accessToken));

        return $client;
    }

    // the rest is set in google_apiclient.yaml.
    private function setClientExtraData(Client $client): void
    {
        $client->setRedirectUri(
            $this->router->generate(
                'app_google_authorize_response', [],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        $client->addScope(Drive::DRIVE);
    }
}
