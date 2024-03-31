<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use Google\Service\Drive;
use Google\Service\Drive\FileList;
use Google\Service\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GoogleDriveService extends BaseGoogleService
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws Exception
     * @throws \Google\Exception
     * @throws ContainerExceptionInterface
     */
    public function getFilesForUser(?string $token): ?FileList
    {
        if (null === $token) {
            return null;
        }

        $service = new Drive($this->getClient($token));

        return $service->files->listFiles();
    }
}
