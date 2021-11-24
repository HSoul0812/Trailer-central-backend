<?php

namespace App\Services\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\FilterRepositoryInterface;
use App\Http\Requests\Marketing\Facebook\CreateMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\UpdateMarketplaceRequest;

/**
 * Class MarketplaceService
 * 
 * @package App\Services\Marketing\Facebook
 */
class MarketplaceService implements MarketplaceServiceInterface
{
    /**
     * @var MarketplaceRepositoryInterface
     */
    protected $marketplace;

    /**
     * @var FilterRepositoryInterface
     */
    protected $filters;

    /**
     * Construct Facebook Marketplace Service
     * 
     * @param MarketplaceRepositoryInterface $marketplace
     * @param FilterRepositoryInterface $filters
     */
    public function __construct(
        MarketplaceRepositoryInterface $marketplace,
        FilterRepositoryInterface $filters
    ) {
        $this->marketplace = $marketplace;
        $this->filters = $filters;
    }

    /**
     * Create Marketplace
     * 
     * @param CreateMarketplaceRequest $request
     * @return Marketplace
     */
    public function create(CreateMarketplaceRequest $request): Marketplace {
        // Create Marketplace Integration
        $marketplace = $this->marketplace->create($request->all());

        // Create All Filters
        if($request->filters && is_array($request->filters)) {
            foreach($request->filters as $filter) {
                $this->filter->create($marketplace->id, $filter->type, $filter->value);
            }
        }

        // Return Response
        return $marketplace;
    }

    /**
     * Update Marketplace
     * 
     * @param UpdateMarketplaceRequest $request
     * @return Marketplace
     */
    public function update(UpdateMarketplaceRequest $request): Marketplace {
        // Update Marketplace Integration
        $marketplace = $this->marketplace->update($request->all());

        // Delete Existing Filters
        $this->filters->deleteAll($marketplace->id);

        // Create All Filters
        if($request->filters && is_array($request->filters)) {
            foreach($request->filters as $filter) {
                $this->filter->create($marketplace->id, $filter->type, $filter->value);
            }
        }

        // Return Response
        return $marketplace;
    }

    /**
     * Delete Marketplace
     * 
     * @param int $id
     * @return boolean
     */
    public function delete(int $id): bool {
        // Delete Filters for Marketplace Integration
        $this->filters->deleteAll($id);

        // Delete Marketplace
        return $this->marketplace->delete($id);
    }
}
