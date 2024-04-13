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
        $textFile = array_filter($files, static function($file) use ($imageName) {
            return str_contains($file->getName(), $imageName);
        });

        // if more than one text file has the same name, just take the first one.
        return $textFile[0];
    }
}
