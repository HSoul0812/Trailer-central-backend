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
        $fbMinPrice = INVENTORY::MIN_PRICE_FOR_FACEBOOK;
        $minDescriptionLength = INVENTORY::MIN_DESCRIPTION_LENGTH_FOR_FACEBOOK;

        // Initialize Inventory Query
        $query = Inventory::select(Inventory::getTableName() . '.*')
            ->where('dealer_id', '=', $integration->dealer_id)
            ->where('show_on_website', 1)
            ->whereRaw("IFNULL({$inventoryTableName}.manufacturer, '') <> ''")
            ->whereRaw("IFNULL({$inventoryTableName}.model, '') <> ''")
            ->whereRaw("IFNULL({$inventoryTableName}.is_archived, 0) = 0")
            ->whereRaw("IFNULL({$inventoryTableName}.status, -1) NOT IN (2,6)")
            ->where("{$inventoryTableName}.year", '<', '2025')
            ->whereNotIn("{$inventoryTableName}.entity_type_id", [EntityType::ENTITY_TYPE_BUILDING, EntityType::ENTITY_TYPE_VEHICLE])
            ->where(function ($query) use ($inventoryTableName, $fbMinPrice) {
                $query->whereRaw("IFNULL($inventoryTableName.sales_price, 0) > $fbMinPrice")
                    ->orWhereRaw("($inventoryTableName.use_website_price AND IFNULL($inventoryTableName.website_price, 0) > $fbMinPrice)")
                    ->orWhereRaw("IFNULL($inventoryTableName.price, 0) > $fbMinPrice");
            })
            ->where(function ($query) use ($inventoryTableName, $minDescriptionLength) {
                $query->whereRaw("LENGTH(IFNULL({$inventoryTableName}.description, '')) >= " . $minDescriptionLength)
                    ->orWhereRaw("LENGTH(IFNULL({$inventoryTableName}.description_html, '')) >= " . (2 * $minDescriptionLength));
            });

        // Only the inventory with images
        $query = $query->has('orderedImages');

        // Join with Listings
        $query = $query->leftJoin(Listings::getTableName(), function ($join) use ($integration, $listingsTableName, $inventoryTableName) {
            $statusDeleted = Listings::STATUS_DELETED;
            $statusExpired = Listings::STATUS_EXPIRED;

            $join->on("$listingsTableName.inventory_id", '=', "$inventoryTableName.inventory_id");
            $join->on("$listingsTableName.marketplace_id", '=', DB::raw($integration->id));
            $join->on(DB::raw("$listingsTableName.status NOT IN ('$statusDeleted', '$statusExpired')"), '=', DB::raw(1));
        })->whereNull("$listingsTableName.id");

        // Skip Integrations With Non-Expired Errors
        $query = $query->leftJoin(Error::getTableName(), function ($join) {
            $join->on(Error::getTableName() . '.inventory_id', '=', Inventory::getTableName() . '.inventory_id');
            $join->on(Error::getTableName() . '.dismissed', '=', DB::raw(0));
            $join->on(Error::getTableName() . '.expires_at', '>', DB::raw('NOW()'));
            $join->on(Error::getTableName() . '.created_at', '>', Inventory::getTableName() . '.updated_at');
        })->whereNull(Error::getTableName() . '.id');

        // Append Location if needed
        if ($integration->dealer_location_id !== 0) {
            $query = $query->where('dealer_location_id', '=', $integration->dealer_location_id);
        }

        // Append Filters
        if (!empty($integration->filter_map)) {
            $query = $query->where(function (Builder $query) use ($integration) {
                foreach ($integration->filter_map as $type => $values) {
                    $query = $query->orWhereIn(Filter::FILTER_COLUMNS[$type], $values);
                }
            });
        }

        $query = $query->with(['attributeValues', 'orderedImages', 'dealerLocation']);
        // Set Sort By
        $query = $query->orderBy("{$inventoryTableName}.created_at", "asc");
        $query = $query->limit($params['per_page'] ?? config('marketing.fb.settings.limit.listings'));

        return $query->get();
    }

    /**
     * Get All Inventory To Delete on Facebook
     *
     * @param Marketplace $integration
     * @param array $params
     * @return Collection<Listings>
     */
    public function getAllSold(Marketplace $integration, array $params): Collection
    {
        // Initialize Inventory Query
        $query = Listings::select(Listings::getTableName() . '.*')
            ->join(Marketplace::getTableName(), Listings::getTableName() . '.marketplace_id', '=', Marketplace::getTableName() . '.id')
            ->join(Inventory::getTableName(), Listings::getTableName() . '.inventory_id', '=', Inventory::getTableName() . '.inventory_id')
            ->where(Listings::getTableName() . '.username', '=', $integration->fb_username)
            ->where(Listings::getTableName() . '.status', Listings::STATUS_ACTIVE)
            ->where(function (Builder $query) {
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
        $query = $query->with(['marketplace', 'inventory', 'inventory.attributeValues', 'inventory.dealerLocation']);
        $query = $query->orderBy(Listings::getTableName() . ".created_at", "asc");
        $query = $query->limit($params['per_page'] ?? config('marketing.fb.settings.limit.sold_updates'));

        return $query->get();
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
