<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Google;

use App\Model\BaseGoogleResponse;

interface GoogleResponseInterface
{
    public function handleResponse(array $data): BaseGoogleResponse;
}
