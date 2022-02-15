<?php

declare(strict_types=1);

namespace App\Services\Common;

use Illuminate\Contracts\Encryption\Encrypter;

/**
 * Describes a encrypter service
 */
interface EncrypterServiceInterface extends Encrypter
{
    /**
     * @inheritDoc
     */
    public function encrypt($value, $serialize = true): string;

    /**
     * @inheritDoc
     */
    public function decrypt($payload, $unserialize = true): string;

    /**
     * @param  string  $value
     * @param  string  $salt
     * @return string
     */
    public function encryptBySalt(string $value, string $salt): string;

    /**
     * @param  string  $payload
     * @param  string  $salt
     * @return string
     */
    public function decryptBySalt(string $payload, string $salt): string;
}
