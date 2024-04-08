<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Helper;

use App\Entity\UserSettings;

/**
 * Helper class to check if the token is valid.
 * It checks if the token is expired or not based on unix time.
 */
class TokenHelper
{
    public function isValid(UserSettings $userSettings): bool
    {
        $expiry = $userSettings->getGoogleDriveTokenExpiry();

        if (null === $expiry) {
            return false;
        }

        $tokenIssueTime = $userSettings->getGoogleDriveTokenIssueTime();

        if (null === $tokenIssueTime) {
            return false;
        }

        $expiryTime = $tokenIssueTime + $expiry;
        $currentTime = time();

        return $currentTime < $expiryTime;
    }
}
