<?php

namespace App\DoctrineEncrypt\Encryptors;

/**
 * Encryptor interface for encryptors
 */
interface EncryptorInterface
{
    /**
     * Must accept data and return encrypted data
     * @param string $data
     * @return string
     */
    public function encrypt($data);

    /**
     * Must accept data and return decrypted data
     * @param string $data
     * @return string
     */
    public function decrypt($data);
}
