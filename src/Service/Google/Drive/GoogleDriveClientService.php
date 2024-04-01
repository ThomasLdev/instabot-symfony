<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google\Drive;

use App\Entity\UserSettings;
use App\Model\GoogleDriveResponse;
use App\Service\Google\GoogleClientService;
use Google\Service\Drive;
use Google\Service\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GoogleDriveClientService
{
    public function __construct(
        private readonly GoogleClientService $clientService,
        private readonly GoogleDriveResponseService $responseService
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function getFilesForUser(UserSettings $userSettings): GoogleDriveResponse
    {
        $folderId = $userSettings->getGoogleDriveFolderId();
        $hasBeenAuthorized = (null !== $userSettings->getGoogleDriveAuthCode());

        if (false === $hasBeenAuthorized) {
            return $this->responseService->handleResponse([
                'error' => 'errors.drive.no_auth',
            ]);
        }

        if (null === $folderId) {
            return $this->responseService->handleResponse([
                'error' => 'errors.drive.no_folder',
            ]);
        }

        return $this->responseService->handleResponse([
            'files' => $this->getFiles($userSettings, $folderId),
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function getFiles(UserSettings $userSettings, string $folderId): array
    {
        return (new Drive($this->clientService->getClientForUser($userSettings)))
            ->files->listFiles($this->getQueryParameters($folderId))->getFiles();
    }

    private function getQueryParameters(string $folderId): array
    {
        return [
            'q' => "'" . $folderId . "' in parents and trashed = false",
        ];
    }
}
