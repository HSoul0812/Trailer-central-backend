<?php

namespace App\Repositories\Marketing\Facebook;

use App\Models\Marketing\Facebook\Error;
use App\Repositories\Repository;
use Illuminate\Support\Collection;

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
     * @return Collection<Error>
     */
    public function dismissAll(int $marketplaceId): Collection;
}