<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Tasks;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\UserSettings;
use App\Helper\FilesHelper;
use App\Model\GoogleDriveResponse;
use App\Service\Google\Drive\GoogleDriveClientService;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use SodiumException;

class TaskRunnerService
{
    public function __construct(
        private readonly GoogleDriveClientService $googleDriveService,
        private readonly LoggerInterface $logger,
        private readonly FilesHelper $filesHelper
    ) {
    }

    /**
     * @throws Exception
     */
    public function run(Task $task): void
    {
        /** @var User $user */
        $user = $task->getUser();
        $username = $user->getUserIdentifier();

        $settings = $user->getSettings();

        if (false === $settings instanceof UserSettings) {
            $this->logger->error('No settings found for user ' . $username);

            return;
        }

        $files = $this->getFilesFromDrive($username, $settings)?->getFiles();

        if (!$files) {
            $this->logger->error('No files found for user ' . $username);

            return;
        }

        $fileToPost = $this->filesHelper->getFileToPost($files);

        if (empty($fileToPost)) {
            $this->logger->error('No images found in files for user ' . $username);

            return;
        }

        try {
            $localFiles = $this->googleDriveService->downloadFiles($fileToPost, $settings);
        } catch (\Google\Service\Exception | ContainerExceptionInterface | RandomException | SodiumException) {
            $this->logger->error('Error when trying to download files for user ' . $username);

            return;
        }

        $test = $localFiles;

        //        $this->graphApiService->postImage($localFiles, $settings);
    }

    /**
     * @throws Exception
     */
    private function getFilesFromDrive(string $username, UserSettings $settings): ?GoogleDriveResponse
    {
        try {
            return $this->googleDriveService->getFilesForUser($settings);
        } catch (Exception $e) {
            $this->logger->error(
                'Error while fetching files from Google Drive for user ' . $username . ': ' . $e->getMessage()
            );

            return null;
        }
    }
}
