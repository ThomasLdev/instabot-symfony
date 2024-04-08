<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use App\Model\BaseGoogleResponse;

interface GoogleResponseInterface
{
    public const ERROR_KEY = 'error';
    public const ACCESS_TOKEN_KEY = 'access_token';

    public function handleResponse(array $data): BaseGoogleResponse;
}
