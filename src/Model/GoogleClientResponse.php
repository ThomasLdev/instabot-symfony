<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Model;

class GoogleClientResponse extends BaseGoogleResponse
{
    private string $accessToken;

    public function getToken(): string
    {
        return $this->accessToken;
    }

    public function setToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
