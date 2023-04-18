<?php

namespace App\Repositories\Marketing\Facebook;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dealer\DealerFBMOverview;
use App\Models\Marketing\Facebook\Error;
use App\Models\Marketing\Facebook\Listings;
use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User\User;

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
        ],
        'last_attempt_ts' => [
            'field' => 'dealer_fbm_overview.last_attempt_ts',
            'direction' => 'DESC'
        ],
        '-last_attempt_ts' => [
            'field' => 'dealer_fbm_overview.last_attempt_ts',
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
     * @return LengthAwarePaginator of Marketplaces
     */
    public function getAll($params): LengthAwarePaginator
    {
        $query = Marketplace::select(Marketplace::getTableName() . '.*', DealerFBMOverview::getTableName() . '.last_attempt_ts')
                            ->where(Marketplace::getTableName() . '.id', '>', 0)
                            ->leftJoin(
                                Listings::getTableName(),
                                Listings::getTableName() . '.marketplace_id',
                                '=',
                                Marketplace::getTableName() . '.id'
                            )
                            ->leftJoin(
                                DealerFBMOverview::getTableName(),
                                DealerFBMOverview::getTableName() . '.id',
                                '=',
                                Marketplace::getTableName() . '.id'
                            );

        if (!isset($params['per_page'])) {
            $params['per_page'] = 1000;
        }

        if (isset($params['dealer_id'])) {
            $query = $query->where(Marketplace::getTableName() . '.dealer_id', $params['dealer_id']);
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where(Marketplace::getTableName() . '.dealer_location_id', $params['dealer_location_id']);
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
                                    DB::raw('DATE_SUB(NOW(), INTERVAL ' . $params['import_range'] . ' HOUR)'))
                      ->orWhereNull(Marketplace::getTableName() . '.imported_at');
            });
        }

        // Skip Integrations With Non-Expired Errors
        if (!empty($params['skip_errors'])) {
            $query = $query->leftJoin(Error::getTableName(), function($join) {
                $join->on(Error::getTableName() . '.marketplace_id', '=',
                                        Marketplace::getTableName() . '.id')
                     ->where(Error::getTableName().'.dismissed', 0)
                     ->whereNull(Error::getTableName().'.inventory_id');
            })->where(function(Builder $query) {
                return $query->whereNull(Error::getTableName().'.id')
                             ->orWhere(Error::getTableName().'.expires_at', '<', DB::raw('NOW()'));
            });
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->groupBy(Marketplace::getTableName() . '.id')
            ->paginate($params['per_page'])->appends($params);
    }

    public function getAllIntegrations($params): Collection
    {
        $query = Marketplace::whereHas('user.dealerClapp')
            ->where('retry_after_ts', '<', DB::raw('NOW()'))->orWhereNull('retry_after_ts');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        } else {
            $query->orderBy('last_attempt_ts', 'ASC');
        }

        return $query->limit($params['per_page'])->get();
    }

    /**
     * Update Marketplace
     *
     * @param array $params
     * @return Marketplace
     */
    public function update($params)
    {
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
