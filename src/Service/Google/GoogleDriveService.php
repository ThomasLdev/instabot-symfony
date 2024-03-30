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
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class GoogleDriveService extends BaseGoogleService
{
    public function __construct(
        ContainerBagInterface $params,
        Filesystem $filesystem
    ) {
        parent::__construct($params, $filesystem);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws Exception
     * @throws \Google\Exception
     * @throws ContainerExceptionInterface
     */
    public function getFilesForUser(): ?FileList
    {
        $service = new Drive($this->getClient());

        return $service->files->listFiles();
    }
}
