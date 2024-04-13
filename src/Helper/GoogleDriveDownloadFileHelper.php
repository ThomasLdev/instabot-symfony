<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Helper;

use Symfony\Component\Filesystem\Filesystem;

class GoogleDriveDownloadFileHelper
{
    public function __construct(
        private readonly Filesystem $filesystem
    ) {
    }

    public function storeAndGetPath(string $blob, string $fileName): string
    {
        $path = $this->getTempDir() . DIRECTORY_SEPARATOR .  $fileName;

        file_put_contents($path, $blob);

        return $path;
    }

    private function getTempDir(): string
    {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tasks-files';

        if (false === $this->filesystem->exists($tempDir)) {
            $this->filesystem->mkdir($tempDir);
        }

        return $tempDir;
    }

    private function clearTaskTempDir(): void
    {
        $tempDir = $this->getTempDir();

        $this->filesystem->remove($tempDir);
    }
}
