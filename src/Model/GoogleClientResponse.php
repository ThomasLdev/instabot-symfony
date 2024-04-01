<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Model;

class GoogleClientResponse extends BaseGoogleResponse
{
    private string $accessToken;

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
