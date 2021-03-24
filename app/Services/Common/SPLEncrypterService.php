<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Exceptions\NotImplementedException;

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
