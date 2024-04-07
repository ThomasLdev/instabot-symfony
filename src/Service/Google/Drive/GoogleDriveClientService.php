<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google\Drive;

use App\Entity\UserSettings;
use App\Model\GoogleDriveResponse;
use App\Service\Google\GoogleClientService;
use Exception;
use Google\Service\Drive;

class GoogleDriveClientService
{
    public function __construct(
        private readonly GoogleClientService $clientService,
        private readonly GoogleDriveResponseService $responseService
    ) {
    }

    /**
     * @throws Exception
     */
    public function getFilesForUser(UserSettings $userSettings): GoogleDriveResponse
    {
        $folderId = $userSettings->getGoogleDriveFolderId();
        $authCode = $userSettings->getGoogleDriveAuthCode();

        // if no token, it can be refreshed, but the authCode is mandatory.
        $hasBeenAuthorized = ('' !== $authCode && null !== $authCode);

        if (false === $hasBeenAuthorized) {
            return $this->responseService->handleResponse([
                'error' => 'errors.drive.no_auth',
            ]);
        }

        // we only query a single folder, not a whole drive.
        if ('' === $folderId || null === $folderId) {
            return $this->responseService->handleResponse([
                'error' => 'errors.drive.no_folder',
            ]);
        }

        $files = $this->getFiles($userSettings, $folderId);

        if (true === array_key_exists('error', $files)) {
            return $this->responseService->handleResponse([
                'error' => 'errors.drive.bad_request',
            ]);
        }

        return $this->responseService->handleResponse([
            'files' => $files,
        ]);
    }

    /**
     * @throws Exception
     */
    private function getFiles(UserSettings $userSettings, string $folderId): array
    {
        try {
            $client = $this->clientService->getClientForUser($userSettings);
            $files = (new Drive($client))->files->listFiles($this->getQueryParameters($folderId))->getFiles();
        } catch (Exception $e) {
            return [
                'error' => 'errors.drive.general',
                'code' => $e->getCode(),
            ];
        }

        return $files;
    }

    private function getQueryParameters(string $folderId): array
    {
        return [
            'q' => "'" . $folderId . "' in parents and trashed = false",
        ];
    }
}
