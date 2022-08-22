<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Exceptions\NotImplementedException;

/**
 * @todo this entire should be replace by a Laravel one like `LaravelHashEncrypterService` as fast we can
 *
 * Sadly this is technical debt which we need to pay, at least while we make a space to really fix it.
 *
 * @see https://www.php.net/manual/en/function.crypt.php
 * @see https://dev.mysql.com/doc/refman/5.6/en/encryption-functions.html#function_encrypt
 *
 * MySQL ENCRYPT() and PHP encrypt() relies on the crypt() system call.
 *
 * It ignores all but the first eight characters of str, at least on some systems. This behavior is determined
 * by the implementation of the underlying crypt() system call.
 */
class SPLEncrypterService implements EncrypterServiceInterface
{
    /**
     * @inheritDoc
     */
    public function encrypt($value, $serialize = true): string
    {
        throw new NotImplementedException('Not implemented yet');
    }

    /**
     * @inheritDoc
     */
    public function decrypt($payload, $unserialize = true): string
    {
        throw new NotImplementedException('Not implemented yet');
    }

    /**
     * Temporal implementation while a refactor to a standard way to crypting come
     *
     * @inheritDoc
     */
    public function encryptBySalt(string $value, string $salt): string
    {
        return crypt($value, $salt);
    }

    /**
     * Temporal implementation while a refactor to a standard way to decrypting come
     *
     * @inheritDoc
     */
    public function decryptBySalt(string $payload, string $salt): string
    {
        return decrypt($payload, $salt);
    }
}
