<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\UserSettings;
use Exception;
use Google\Client;
use Google\Service\Drive;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

trait GoogleClientTrait
{
    private const GOOGLE_API_KEY = 'google_api_key';
    private const GOOGLE_CLIENT_ID = 'google_client_id';
    private const GOOGLE_CLIENT_SECRET = 'google_client_secret';
    private const GOOGLE_APP_NAME = 'Instabot';

    public function __construct(
        private readonly ContainerBagInterface  $params,
        private readonly GoogleOAuthTokenService $tokenService
    ) {
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function getClientForUser(UserSettings $userSettings): Client
    {
        $client = new Client();

        $this->setClientBaseData($client);

        $authCode = $userSettings->getGoogleDriveAuthCode();
        $accessToken = $userSettings->getGoogleDriveToken();

        if (!$accessToken && !$authCode) {
            return $client;
        }

        $client->setAccessToken($this->tokenService->getToken($accessToken, $authCode, $client, $userSettings));

        return $client;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function setClientBaseData(Client $client): void
    {
        $client->setApplicationName(self::GOOGLE_APP_NAME);
        $client->setDeveloperKey($this->params->get(self::GOOGLE_API_KEY));
        $client->setClientId($this->params->get(self::GOOGLE_CLIENT_ID));
        $client->setClientSecret($this->params->get(self::GOOGLE_CLIENT_SECRET));
        $client->setAccessType('offline');
        $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/google/authorize-response');
        $client->addScope(Drive::DRIVE);
    }
}
