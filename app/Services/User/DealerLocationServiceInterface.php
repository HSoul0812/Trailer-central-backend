<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User\DealerLocation;

interface DealerLocationServiceInterface
{
    public const TAX_CATEGORY_DEFAULT = 'All';
    public const TAX_CATEGORY_TRAILER = 'Trailer or Truck Bed';
    public const TAX_CATEGORY_RV = 'Recreational Vehicles';
    public const TAX_CATEGORY_VEHICLE = 'Vehicle';
    public const TAX_CATEGORY_WATERCRAFT = 'Watercraft';

    public const AVAILABLE_TAX_CATEGORIES = [
        0 => self::TAX_CATEGORY_DEFAULT,
        1 => self::TAX_CATEGORY_TRAILER,
        3 => self::TAX_CATEGORY_RV,
        4 => self::TAX_CATEGORY_VEHICLE,
        5 => self::TAX_CATEGORY_WATERCRAFT
    ];

    public function create(int $dealerId, array $params): DealerLocation;

    public function update(int $locationId, int $dealerId, array $params): DealerLocation;

    public function getAnotherAvailableLocationIdToMove(int $locationId, int $dealerId): ?int;

    public function moveAndDelete(int $id, ?int $moveToLocationId = null): bool;

    public function moveRelatedRecords(DealerLocation $location, ?int $moveToLocationId = null): bool;
}
