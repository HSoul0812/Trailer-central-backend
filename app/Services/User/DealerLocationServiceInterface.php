<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User\DealerLocation;

interface DealerLocationServiceInterface
{
    public function create(int $dealerId, array $params): DealerLocation;

    public function update(int $locationId, int $dealerId, array $params): bool;

    public function getAnotherAvailableLocationIdToMove(int $locationId, int $dealerId): ?int;

    public function moveAndDelete(int $id, ?int $moveToLocationId = null): bool;

    public function moveRelatedRecords(DealerLocation $location, ?int $moveToLocationId = null): bool;
}