<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google\Drive;

use App\Entity\UserSettings;
use App\Helper\GoogleDriveDownloadFileHelper;
use App\Model\GoogleDriveResponse;
use App\Service\Google\GoogleClientService;
use Exception;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Random\RandomException;

class GoogleDriveClientService
{
    public function __construct(
        private readonly GoogleClientService $clientService,
        private readonly GoogleDriveResponseService $responseService,
        private readonly GoogleDriveDownloadFileHelper $downloadFileHelper
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
     * @throws NotFoundExceptionInterface
     * @throws \Google\Service\Exception
     * @throws RandomException
     * @throws ContainerExceptionInterface
     * @throws \SodiumException
     */
    public function downloadFiles(array $filesToPost, UserSettings $userSettings): array
    {
        $localFilesPaths = [];
        $client = $this->clientService->getClientForUser($userSettings);
        $driveClient = new Drive($client);

        /** @var DriveFile $file */
        foreach ($filesToPost as $file) {
            $fileMetadata = $driveClient->files->get($file->getId(), [
                'alt' => 'media',
            ]);

            /** @var Response $fileMetadata */
            $localFilesPaths[] = $this->downloadFileHelper->storeAndGetPath(
                $fileMetadata->getBody()->getContents(),
                $file->getName()
            );
        }

        return $localFilesPaths;
    }

    /**
     * @throws Exception
     */
    private function getFiles(UserSettings $userSettings, string $folderId): array
    {
        try {
            $client = $this->clientService->getClientForUser($userSettings);
            $files = (new Drive($client))->files->listFiles($this->getListQueryParameters($folderId))->getFiles();
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface | Exception $e) {
            return [
                'error' => 'errors.drive.general',
                'code' => $e->getCode(),
            ];
        }

        return $files;
    }

    private function getListQueryParameters(string $folderId): array
    {
        return [
            'q' => "'" . $folderId . "' in parents and trashed = false",
            'orderBy' => 'createdTime',
        ];
    }
}
