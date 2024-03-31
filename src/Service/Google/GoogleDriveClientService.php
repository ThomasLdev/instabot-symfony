<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use App\Entity\UserSettings;
use Google\Service\Drive;
use Google\Service\Drive\FileList;
use Google\Service\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GoogleDriveClientService
{
    use GoogleClientTrait;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function getFilesForUser(UserSettings $userSettings): ?FileList
    {
        $folderId = $userSettings->getGoogleDriveFolderId();
        $hasBeenAuthorized = ($userSettings->getGoogleDriveAuthCode() !== null);

        if (false === $hasBeenAuthorized) {
            throw new Exception('You did not authorize the app.');
        }

        if (null === $folderId) {
            throw new Exception('No folder ID found for user.');
        }

        $service = new Drive($this->getClientForUser($userSettings));

        return $service->files->listFiles([
            'q' => "'".$folderId."' in parents and trashed = false",
            'pageSize' => 50,
        ]);
    }

    private function handleResponse($response): ?FileList
    {
        if ($response instanceof FileList) {
            return $response;
        }

        return null;
    }
}
