<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google\OAuth;

use App\Model\GoogleClientResponse;
use App\Service\Google\GoogleResponseInterface;

class GoogleOAuthResponseService implements GoogleResponseInterface
{
    private const ERROR_KEY = 'error';
    private const ACCESS_TOKEN_KEY = 'access_token';

    public function handleResponse(array $data): GoogleClientResponse
    {
        if (array_key_exists(self::ERROR_KEY, $data)) {
            return $this->setResponse(false, $data['error']);
        }

        if (false === array_key_exists(self::ACCESS_TOKEN_KEY, $data)) {
            return $this->setResponse(false, 'No access token found.');
        }

        return $this->setResponse(true, '', $data[self::ACCESS_TOKEN_KEY]);
    }

    private function setResponse(bool $success, string $message, ?string $token = null): GoogleClientResponse
    {
        return (new GoogleClientResponse())
            ->setSuccess($success)
            ->setMessage($message)
            ->setAccessToken($token ?? '');
    }
}
