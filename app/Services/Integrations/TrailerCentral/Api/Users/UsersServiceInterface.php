<?php

namespace App\Services\Integrations\TrailerCentral\Api\Users;

use App\DTOs\User\TcApiResponseUser;

interface UsersServiceInterface
{
    public function create(array $attributes): TcApiResponseUser;

    public function get(string $email): TcApiResponseUser;
}
