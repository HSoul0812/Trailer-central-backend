<?php

namespace App\Repositories\Marketing\Facebook;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Facebook\Error;
use App\Models\Marketing\Facebook\Listings;
use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class MarketplaceRepository implements MarketplaceRepositoryInterface {
    use SortTrait, Transaction;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'username' => [
            'field' => 'fbapp_marketplace.fb_username',
            'direction' => 'DESC'
        ],
        '-username' => [
            'field' => 'fbapp_marketplace.fb_username',
            'direction' => 'ASC'
        ],
        'location' => [
            'field' => 'fbapp_marketplace.dealer_location_id',
            'direction' => 'DESC'
        ],
        '-location' => [
            'field' => 'fbapp_marketplace.dealer_location_id',
            'direction' => 'ASC'
        ],
        'imported' => [
            [
                'field' => 'MAX(fbapp_listings.created_at)',
                'direction' => 'DESC'
            ],
            [
                'field' => 'fbapp_marketplace.created_at',
                'direction' => 'DESC'
            ]
        ],
        '-imported' => [
            [
                'field' => 'MIN(fbapp_listings.created_at)',
                'direction' => 'ASC'
            ],
            [
                'field' => 'fbapp_marketplace.created_at',
                'direction' => 'ASC'
            ]
        ],
        'created_at' => [
            'field' => 'fbapp_marketplace.created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'fbapp_marketplace.created_at',
            'direction' => 'ASC'
        ],
        'updated_at' => [
            'field' => 'fbapp_marketplace.updated_at',
            'direction' => 'DESC'
        ],
        '-updated_at' => [
            'field' => 'fbapp_marketplace.updated_at',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Facebook Marketplace
     * 
     * @param array $params
     * @return Marketplace
     */
    public function create($params) {
        // Create Marketplace
        return Marketplace::create($params);
    }

    /**
     * Delete Marketplace
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        // Delete Marketplace
        return Marketplace::findOrFail($id)->delete();
    }

    /**
     * Get Marketplace
     * 
     * @param array $params
     * @return Marketplace
     */
    public function get($params) {
        // Find Marketplace By ID
        return Marketplace::findOrFail($params['id']);
    }

    /**
     * Get All Marketplaces That Match Params
     * 
     * @param array $params
     * @return Collection of Marketplaces
     */
    public function getAll($params) {
        $query = Marketplace::select(Marketplace::getTableName() . '.*')
                            ->where(Marketplace::getTableName() . '.id', '>', 0)
                            ->leftJoin(Listings::getTableName(),
                                        Listings::getTableName() . '.marketplace_id', '=',
                                        Marketplace::getTableName() . '.id');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn(Marketplace::getTableName() . '.id', $params['id']);
        }

        // Exclude Integration ID's
        if (isset($params['exclude'])) {
            $query = $query->whereNotIn(Marketplace::getTableName() . '.id', $params['exclude']);
        }

        // Import Range Provided
        if (!empty($params['import_range'])) {
            $query = $query->where(function(Builder $query) use($params) {
                $query->where(Marketplace::getTableName() . '.imported_at', '<',
                                    DB::raw('DATE_SUB(NOW(), INTERVAL ' . $params['import_range'] . ' HOUR)')
                      ->orWhereNull(Marketplace::getTableName() . '.imported_at'));
            });
        }

        // Skip Integrations With Non-Expired Errors
        if (isset($params['skip_errors'])) {
            $query = $query->leftJoin(Error::getTableName(), function($join) {
                $join->on(Error::getTableName() . '.marketplace_id', '=',
                                        Marketplace::getTableName() . '.id')
                     ->whereNull(Error::getTableName().'.inventory_id');
            })->where(function(Builder $query) {
                return $query->whereNull(Error::getTableName().'.id')
                              ->orWhere(function(Builder $query) {
                    return $query->where(Error::getTableName().'.dismissed', 0)
                                 ->where(Error::getTableName().'.expires_at', '<', DB::raw('NOW()'));
                });
            });
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->groupBy(Marketplace::getTableName() . '.id')
                     ->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Marketplace
     * 
     * @param array $params
     * @return Marketplace
     */
    public function update($params) {
        $marketplace = Marketplace::findOrFail($params['id']);

        DB::transaction(function() use (&$marketplace, $params) {
            // Fill Marketplace Details
            $marketplace->fill($params)->save();
        });

        return $marketplace;
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
