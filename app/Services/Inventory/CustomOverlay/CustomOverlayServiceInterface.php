<?php

declare(strict_types=1);

namespace App\Services\Inventory\CustomOverlay;

use App\Models\Inventory\CustomOverlay;
use Illuminate\Database\Eloquent\Collection;

interface CustomOverlayServiceInterface
{
    /**
     * @param int $dealerId
     * @return array<CustomOverlay>|Collection
     */
    public function list(int $dealerId): Collection;

    public function bulkUpsert(int $dealerId, array $overlays): bool;

    public function upsert(int $dealerId, string $name, string $value): bool;
}
