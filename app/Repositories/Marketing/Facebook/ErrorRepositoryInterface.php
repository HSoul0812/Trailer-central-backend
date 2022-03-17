<?php

namespace App\Repositories\Marketing\Facebook;

use App\Models\Marketing\Facebook\Error;
use App\Repositories\Repository;

interface ErrorRepositoryInterface extends Repository {
    /**
     * Dismiss Error on Marketplace Integration
     * 
     * @param int $marketplaceId
     * @param int|null $errorId
     * @param Error|null
     */
    public function dismiss(int $marketplaceId, ?int $errorId = null): ?Error;
}