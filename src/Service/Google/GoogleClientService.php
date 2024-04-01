<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\UserSettings;
use App\Service\Google\OAuth\GoogleOAuthTokenService;
use App\Service\Security\EncryptionService;
use Exception;
use Google\Client;
use Google\Service\Drive;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class GoogleClientService
{
    public const GOOGLE_API_KEY = 'google_api_key';
    public const GOOGLE_CLIENT_ID = 'google_client_id';
    public const GOOGLE_CLIENT_SECRET = 'google_client_secret';
    public const GOOGLE_APP_NAME = 'Instabot';

    public function __construct(
        private readonly ContainerBagInterface $containerBag,
        private readonly GoogleOAuthTokenService $OAuthService,
        private readonly EncryptionService $encryptionService
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

        /** @var string $authCode */
        $response = $this->OAuthService->getToken($accessToken, $authCode, $client, $userSettings);

        if (false === $response->getSuccess() || '' === $response->getAccessToken()) {
            return $client;
        }

        $client->setAccessToken($this->encryptionService->decrypt($response->getAccessToken()));

        return $client;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function setClientBaseData(Client $client): void
    {
        $params = $this->getRequiredParameters();

        $client->setApplicationName(self::GOOGLE_APP_NAME);
        $client->setDeveloperKey($params[self::GOOGLE_API_KEY]);
        $client->setClientId($params[self::GOOGLE_CLIENT_ID]);
        $client->setClientSecret($params[self::GOOGLE_CLIENT_SECRET]);
        $client->setAccessType('offline');
        $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/google/authorize-response');
        $client->addScope(Drive::DRIVE);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private function getRequiredParameters(): array
    {
        $envParams = [
            self::GOOGLE_API_KEY => $this->containerBag->get(self::GOOGLE_API_KEY),
            self::GOOGLE_CLIENT_ID => $this->containerBag->get(self::GOOGLE_CLIENT_ID),
            self::GOOGLE_CLIENT_SECRET => $this->containerBag->get(self::GOOGLE_CLIENT_SECRET),
        ];

        foreach ($envParams as $param) {
            if (false === is_string($param)) {
                throw new RuntimeException('Google API parameters must be strings.');
            }
        }

        return $envParams;
    }
}
