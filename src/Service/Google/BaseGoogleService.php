<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use Google\Client;
use Google\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseGoogleService
{
    private const GOOGLE_API_KEY = 'google_api_key';
    private const GOOGLE_CLIENT_ID = 'google_client_id';
    private const GOOGLE_APP_NAME = 'Instabot';
    private const GOOGLE_SCOPES = ['https://www.googleapis.com/auth/drive'];

    public function __construct(
        private readonly ContainerBagInterface $params,
        private readonly Filesystem $filesystem
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function getClient(?string $token = null): Client
    {
        $client = new Client();

        $client->setApplicationName(self::GOOGLE_APP_NAME);
        $client->setDeveloperKey($this->params->get(self::GOOGLE_API_KEY));
        $client->setClientId($this->params->get(self::GOOGLE_CLIENT_ID));
        $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/google/authorize-response');
        $client->addScope(self::GOOGLE_SCOPES);

        if (null !== $token) {
            $tempJsonFile = $this->getAuthConfigAsJson($client, $token);
            $client->setAuthConfig($tempJsonFile);
            $this->filesystem->remove($tempJsonFile);
        }

        return $client;
    }

    /**
     * @throws Exception
     */
    public function getAuthConfigAsJson(Client $client, string $token): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'google_oauth');

        if (false === $tempFile) {
            throw new Exception('Failed to create temporary file');
        }

        $fileContent = json_encode($token);

        if (false === $fileContent) {
            throw new Exception('Failed to encode token to JSON');
        }

        file_put_contents($tempFile, $fileContent);

        if (false === $client->setAuthConfig($tempFile)) {
            throw new Exception('Failed to set auth config');
        }

        return $tempFile;
    }
}
