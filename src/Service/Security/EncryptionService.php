<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Security;

use Exception;
use Random\RandomException;
use RuntimeException;
use SodiumException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EncryptionService
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    /**
     * @throws RandomException
     * @throws SodiumException
     * @throws Exception
     */
    public function encrypt(string $value): string
    {
        $nonce = $this->getNonce();
        $encryptedValue = sodium_crypto_secretbox($value, $nonce, $this->getSecretKey());

        return base64_encode($nonce . $encryptedValue);
    }

    /**
     * @throws SodiumException
     * @throws Exception
     */
    public function decrypt(string $encryptedValueWithNonce): ?string
    {
        $decodedValue = base64_decode($encryptedValueWithNonce);
        $nonce = substr($decodedValue, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        if (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES !== strlen($nonce)) {
            return null;
        }

        $encryptedValue = substr($decodedValue, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $value = sodium_crypto_secretbox_open($encryptedValue, $nonce, $this->getSecretKey());

        if (false === $value) {
            return null;
        }

        return $value;
    }

    /**
     * @throws RandomException
     */
    private function getNonce(): string
    {
        return random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    }

    /**
     * @throws Exception
     */
    private function getSecretKey(): string
    {
        $hexKey = $this->parameterBag->get('encryption_key');

        if (false === is_string($hexKey)) {
            throw new RuntimeException('encryption key must be a string');
        }

        // ensure the key is the correct length
        $hexKey = str_pad(
            $hexKey,
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES * 2,
            '0',
            STR_PAD_LEFT
        );

        $binaryKey = sodium_hex2bin($hexKey);

        if (SODIUM_CRYPTO_SECRETBOX_KEYBYTES !== strlen($binaryKey)) {
            throw new RuntimeException('encryption key must be ' . SODIUM_CRYPTO_SECRETBOX_KEYBYTES . ' bytes');
        }

        return $binaryKey;
    }
}
