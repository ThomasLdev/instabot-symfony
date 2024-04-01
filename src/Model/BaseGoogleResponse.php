<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Model;

abstract class BaseGoogleResponse
{
    private bool $success;
    private string $message;

    public function getSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): static
    {
        $this->success = $success;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
