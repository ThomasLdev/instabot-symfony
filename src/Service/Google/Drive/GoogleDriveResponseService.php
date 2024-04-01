<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google\Drive;

use App\Model\GoogleDriveResponse;
use App\Service\Google\GoogleResponseInterface;

class GoogleDriveResponseService implements GoogleResponseInterface
{
    public function handleResponse(array $data): GoogleDriveResponse
    {
        if (array_key_exists('error', $data)) {
            return $this->setResponse(false, $data['error']);
        }

        if (false === array_key_exists('files', $data)) {
            return $this->setResponse(false, 'No files found.');
        }

        return $this->setResponse(true, '', $data['files']);
    }

    private function setResponse(bool $success, string $message, ?array $files = null): GoogleDriveResponse
    {
        return (new GoogleDriveResponse())
            ->setSuccess($success)
            ->setMessage($message)
            ->setFiles($files ?? []);
    }
}
