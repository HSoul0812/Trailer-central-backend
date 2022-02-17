<?php

namespace App\Repositories\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Repository;
use Illuminate\Pagination\LengthAwarePaginator;

interface ListingRepositoryInterface extends Repository {
    /**
     * Get All Inventory Missing on Facebook
     * 
     * @param Marketplace $integration
     * @param array $params
     * @return LengthAwarePaginator<Listings>
     */
    public function getAllMissing(Marketplace $integration, array $params): LengthAwarePaginator;
}