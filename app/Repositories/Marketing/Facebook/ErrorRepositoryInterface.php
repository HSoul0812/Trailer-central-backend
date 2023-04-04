<?php

namespace App\Repositories\Marketing\Facebook;

use App\Models\Marketing\Facebook\Error;
use App\Repositories\Repository;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as DbCollection;

interface ErrorRepositoryInterface extends Repository {
    /**
     * Dismiss Error on Marketplace Integration
     * 
     * @param int $marketplaceId
     * @param int|null $errorId
     * @param Error|null
     */
    public function dismiss(int $marketplaceId, ?int $errorId = null): ?Error;

    /**
     * Dismiss All Errors on Marketplace Integration
     *
     * @param int $marketplaceId
     * @param int $inventoryId
     * @return Collection<Error>
     */
    public function dismissAll(int $marketplaceId, int $inventoryId = 0): Collection;

    /**
     * Remove duplicates in the same day, and only keep the latest
     *
     * @param int $marketplaceId
     * @param ?int $inventoryId
     * @return void
     */
    public function removeDailyDuplicates(int $marketplaceId, ?int $inventoryId = null): void;

    /**
     * Dismiss All Active Errors on Marketplace Integration
     *
     * @param int $marketplaceId
     * @return Collection<Error>
     */
    public function dismissAllActiveForIntegration(int $marketplaceId): Collection;

    /**
     * Get All Active Errors on Dealer
     * 
     * @param int $dealerId
     * @return Collection<Error>
     */
    public function getAllActive(int $dealerId): DbCollection;
}