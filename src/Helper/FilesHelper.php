<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Helper;

use Google\Service\Drive\DriveFile;

class FilesHelper
{
    public function getFileToPost(array $files): array
    {
        $imageFiles = array_filter($files, static function($file) {
            return str_contains($file->getMimeType(), 'image');
        });

        /** @var DriveFile|null $imageFile */
        $imageFile = $imageFiles ? reset($imageFiles) : null;

        if (null === $imageFile) {
            return [];
        }

        return [
            'image' => $imageFile,
            'text' => $this->getAssociatedText($files, $imageFile->getName())
        ];
    }

    private function getAssociatedText(array $files, string $imageName): ?DriveFile
    {
        $textFile = array_filter($files, function($file) use ($imageName) {
            return ($this->getFileNameWithoutType($file->getName()) === $this->getFileNameWithoutType($imageName)
            && str_contains($file->getMimeType(), 'text'));
        });

        // if more than one text file has the same name, just take the first one.
        return array_values($textFile)[0];
    }

    private function getFileNameWithoutType(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }
}
