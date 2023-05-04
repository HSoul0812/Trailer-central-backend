<?php

namespace App\Services\Integrations\TrailerCentral\Api\Users;

use App\DTOs\User\TcApiResponseUser;
use App\DTOs\User\TcApiResponseUserLocation;

interface UsersServiceInterface
{
    public function create(array $attributes): TcApiResponseUser;

    public function get(string $email): TcApiResponseUser;

    public function getLocations(int $userId): array;

    public function createLocation(array $location): TcApiResponseUserLocation;

    public function updateLocation(int $locationId, array $location): TcApiResponseUserLocation;
}
