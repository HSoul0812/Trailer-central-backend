<?php

namespace App\Repositories\Marketing\Facebook;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\Inventory;
use App\Models\Marketing\Facebook\Filter;
use App\Models\Marketing\Facebook\Listings;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Error;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ListingRepository implements ListingRepositoryInterface {
    use SortTrait, Transaction;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'updated_at' => [
            'field' => 'updated_at',
            'direction' => 'DESC'
        ],
        '-updated_at' => [
            'field' => 'updated_at',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Facebook Listing
     * 
     * @param array $params
     * @return Listing
     */
    public function create($params) {
        // Already Exists?!
        if(!empty($params['facebook_id'])) {
            $listing = Listings::where('facebook_id', $params['facebook_id'])->first();
            if(!empty($listing->id)) {
                if($listing->inventory_id === (int) $params['inventory_id']) {
                    return $this->update($params);
                } elseif($listing->inventory_id !== (int) $params['inventory_id']) {
                    $params['facebook_id'] = 0;
                }
            }
        }

        // Create Listing
        return Listings::create($params);
    }

    /**
     * Delete Listing
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        // Delete Listing
        return Listings::findOrFail($id)->delete();
    }

    /**
     * Get Listing
     * 
     * @param array $params
     * @return Listing
     */
    public function get($params) {
        // Find Listing By ID
        return Listings::findOrFail($params['id']);
    }

    /**
     * Get All Listings That Match Params
     * 
     * @param array $params
     * @return Collection<Listings>
     */
    public function getAll($params) {
        $query = Listings::where('marketplace_id', '=', $params['marketplace_id']);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Listing
     * 
     * @param array $params
     * @return Listings
     */
    public function update($params) {
        if(!empty($params['facebook_id'])) {
            $listing = Listings::where('facebook_id', $params['facebook_id'])->firstOrFail();
        } else {
            $listing = Listings::where('id', $params['id'])->firstOrFail();
        }

        DB::transaction(function() use (&$listing, $params) {
            // Fill Listing Details
            $listing->fill($params)->save();
        });

        return $listing;
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }


    /**
     * Get All Inventory Missing on Facebook
     * 
     * @param Marketplace $integration
     * @param array $params
     * @return Collection<Inventory>
     */
    public function getAllMissing(Marketplace $integration, array $params): Collection
    {
        $inventoryTableName = Inventory::getTableName();
        $listingsTableName = Listings::getTableName();
 
        // Initialize Inventory Query
        $query = Inventory::select(Inventory::getTableName().'.*')
            ->where('dealer_id', '=', $integration->dealer_id)
            ->where('show_on_website', 1)
            ->where("{$inventoryTableName}.price", '>', INVENTORY::MIN_PRICE_FOR_FACEBOOK)
            ->where("{$inventoryTableName}.entity_type_id", '<>', EntityType::ENTITY_TYPE_BUILDING)
            ->where("{$inventoryTableName}.entity_type_id", '<>', EntityType::ENTITY_TYPE_VEHICLE)
            ->whereRaw("IFNULL(is_archived, 0) = 0")
            ->whereRaw("IFNULL({$inventoryTableName}.status, -1) NOT IN (2,6)")
            ->whereRaw("LENGTH({$inventoryTableName}.description) >= " . INVENTORY::MIN_DESCRIPTION_LENGTH_FOR_FACEBOOK)
            ->has('orderedImages');

        // Append Join
        $query = $query->leftJoin(Listings::getTableName(), function ($join) use ($integration) {
            $join->on(Listings::getTableName() . '.inventory_id', '=', Inventory::getTableName() . '.inventory_id')
                ->where(Listings::getTableName() . '.username', '=', $integration->fb_username)
                ->where(Listings::getTableName() . '.page_id', '=', $integration->page_id ?? '0');
        });
        $query = $query->where(function (Builder $query) use ($listingsTableName) {
            $query = $query->whereNull("{$listingsTableName}.facebook_id")
            ->orWhereIn("{$listingsTableName}.status", [Listings::STATUS_DELETED, Listings::STATUS_EXPIRED]);
        });

        // Skip Integrations With Non-Expired Errors
        $query = $query->leftJoin(Error::getTableName(), function ($join) {
            $join->on(Error::getTableName() . '.marketplace_id', '=', Inventory::getTableName() . '.inventory_id')
                ->where(Error::getTableName() . '.dismissed', 0);
        })->where(function (Builder $query) {
            return $query->whereNull(Error::getTableName() . '.id')
                ->orWhere(Error::getTableName() . '.expires_at', '<', DB::raw('NOW()'));
        });

        // Append Location
        if (!empty($integration->dealer_location_id)) {
            $query = $query->where('dealer_location_id', '=', $integration->dealer_location_id);
        }

        // Append Filters
        if (!empty($integration->filter_map)) {
            $query = $query->where(function(Builder $query) use($integration) {
                foreach($integration->filter_map as $type => $values) {
                    $query = $query->orWhereIn(Filter::FILTER_COLUMNS[$type], $values);
                }
            });
        }

        $query = $query->with(['attributeValues', 'orderedImages', 'dealerLocation']);
        // Set Sort By
        $query = $query->orderBy("{$inventoryTableName}.created_at", "asc");
        $query = $query->limit($params['per_page'] ?? config('marketing.fb.settings.limit.listings'));

        // Return Paginated Inventory
        return $query->get();
    }

    /**
     * Get All Inventory To Delete on Facebook
     * 
     * @param Marketplace $integration
     * @param array $params
     * @return LengthAwarePaginator<Listings>
     */
    public function getAllSold(Marketplace $integration, array $params): LengthAwarePaginator {
        // Initialize Inventory Query
        $query = Listings::select(Listings::getTableName().'.*')
                          ->where(Listings::getTableName().'.username', '=', $integration->fb_username)
                          ->where(Listings::getTableName().'.page_id', '=', $integration->page_id ?? '0')
                          ->whereNotNull(Listings::getTableName() . '.facebook_id')
                          ->where(Listings::getTableName() . '.status', Listings::STATUS_ACTIVE)
                          ->leftJoin(Inventory::getTableName(), Listings::getTableName() . '.inventory_id',
                                        '=', Inventory::getTableName() . '.inventory_id')
                          ->where(function(Builder $query) {
                                $query = $query->where(Inventory::getTableName() . '.status', 2)
                                               ->orWhere(Inventory::getTableName() . '.status', 6)
                                               ->orWhere(Inventory::getTableName() . '.is_archived', 1)
                                               ->orWhere(Inventory::getTableName() . '.show_on_website', 0)
                                               ->orWhereNull(Inventory::getTableName() . '.inventory_id');
                          });

        if (!isset($params['per_page'])) {
            $params['per_page'] = 20;
        }

        // Require Inventory
        $query = $query->with(['marketplace', 'inventory', 'inventory.attributeValues', 'inventory.orderedImages', 'inventory.dealerLocation']);

        // Return Paginated Inventory
        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Count Inventory posted today on Facebook
     * 
     * @param Marketplace $integration
     * @return int
     */
    public function countFacebookPostings(Marketplace $integration): int
    {
        return Listings::where('marketplace_id', '=', $integration->id)->whereDate('created_at', Carbon::today())->count();
    }
}
