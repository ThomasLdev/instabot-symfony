<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google\OAuth;

use App\Entity\UserSettings;
use App\Helper\TokenHelper;
use App\Model\GoogleClientResponse;
use App\Service\Google\GoogleClientService;
use App\Service\Security\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Random\RandomException;
use SodiumException;

class GoogleOAuthTokenService
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenHelper $tokenHelper,
        private readonly GoogleOAuthResponseService $OAuthResponse,
        private readonly GoogleClientService $googleClientService
    ) {
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     */
    public function storeAuthCodeForUser(UserSettings $userSettings, string $authCode): GoogleClientResponse
    {
        $userSettings->setGoogleDriveAuthCode($this->encryptionService->encrypt($authCode));

        try {
            $authResponse = $this->getAccessToken($userSettings);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface|RandomException|SodiumException $e) {
            return $this->OAuthResponse->handleResponse([]);
        }

        return $authResponse;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws RandomException
     * @throws ContainerExceptionInterface
     * @throws SodiumException
     */
    public function getAccessToken(UserSettings $userSettings, ?Client $client = null): GoogleClientResponse
    {
        $token = $userSettings->getGoogleDriveToken();

        if ((null !== $token) && $this->tokenHelper->isValid($userSettings)) {
            return $this->OAuthResponse->handleResponse([
                'access_token' => $token,
            ]);
        }

        if (null === $client) {
            $client = $this->googleClientService->getClientForUser($userSettings);
        }

        $data = $client->fetchAccessTokenWithAuthCode(
            $this->encryptionService->decrypt($userSettings->getGoogleDriveAuthCode())
        );

        $response = $this->OAuthResponse->handleResponse($data);

        if (false === $response->getSuccess()) {
            return $response;
        }

        $this->refreshUserTokens($userSettings, $data);

        return $response;
    }

//    /**
//     * @throws SodiumException
//     * @throws RandomException
//     */
//    public function getToken(
//        ?string $accessToken,
//        string $authCode,
//        Client $client,
//        UserSettings $userSettings
//    ): GoogleClientResponse {
//        if ((null !== $accessToken) && $this->tokenHelper->isValid($userSettings)) {
//            return $this->OAuthResponse->handleResponse([
//                'access_token' => $accessToken,
//            ]);
//        }
//
//        $token = $this->encryptionService->decrypt($authCode);
//
//        if (null === $token) {
//            return $this->OAuthResponse->handleResponse([
//                'error' => 'errors.oauth.bad_token',
//            ]);
//        }
//
//        $data = $client->fetchAccessTokenWithAuthCode($token);
//        $response = $this->OAuthResponse->handleResponse($data);
//
//        if (false === $response->getSuccess()) {
//            return $response;
//        }
//
//        $this->refreshUserTokens($userSettings, $data);
//
//        return $response;
//    }

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
