<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Model;

class GoogleDriveResponse extends BaseGoogleResponse
{
    private array $files;

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): static
    {
        $this->files = $files;

        return $this;
    }
}
