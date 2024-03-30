<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\Service\Security;

use Exception;
use Random\RandomException;
use SodiumException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TokenService
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
    public function encrypt(string $token): string
    {
        $nonce = $this->getNonce();
        $encryptedToken = sodium_crypto_secretbox($token, $nonce, $this->getSecretKey());

        return base64_encode($nonce . $encryptedToken);
    }

    /**
     * @throws SodiumException
     * @throws Exception
     */
    public function decrypt(string $encryptedTokenWithNonce): string
    {
        $decoded = base64_decode($encryptedTokenWithNonce);
        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encryptedToken = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $token = sodium_crypto_secretbox_open($encryptedToken, $nonce, $this->getSecretKey());

        if ($token === false) {
            throw new Exception('failed to decrypt data');
        }

        return $token;
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
            throw new Exception('encryption key must be a string');
        }

        // ensure the key is the correct length
        $hexKey = str_pad(
            $hexKey,
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES * 2, "0",
            STR_PAD_LEFT
        );

        $binaryKey = sodium_hex2bin($hexKey);

        if (strlen($binaryKey) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new Exception('encryption key must be ' . SODIUM_CRYPTO_SECRETBOX_KEYBYTES . ' bytes');
        }

        return $binaryKey;
    }
}
