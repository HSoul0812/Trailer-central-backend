<?php

namespace App\Services\Marketing\Facebook;

use App\Models\Integration\Facebook\Marketplace;
use App\Http\Requests\Marketing\Facebook\CreateMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\UpdateMarketplaceRequest;

interface MarketplaceServiceInterface {
    /**
     * Create Marketplace
     * 
     * @param CreateMarketplaceRequest $request
     * @return Marketplace
     */
    public function create(CreateMarketplaceRequest $request): Marketplace;

    /**
     * Update Marketplace
     * 
     * @param UpdateMarketplaceRequest $request
     * @return Marketplace
     */
    public function update(UpdateMarketplaceRequest $request): Marketplace;

    /**
     * Delete Marketplace
     * 
     * @param int $id
     * @return boolean
     */
    public function delete(int $id): bool;
}