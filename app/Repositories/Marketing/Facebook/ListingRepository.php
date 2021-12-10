<?php

namespace App\Repositories\Marketing\Facebook;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Inventory;
use App\Models\Marketing\Facebook\Filter;
use App\Models\Marketing\Facebook\Listings;
use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Transaction;
use Grimzy\LaravelMysqlSpatial\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
        $listing = Listings::where('facebook_id', $params['facebook_id'])->first();
        if(!empty($listing->id)) {
            return $this->update($params);
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
        $listing = Listings::where('facebook_id', $params['facebook_id'])->firstOrFail();

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
     * @return Collection<Listings>
     */
    public function getAllMissing(Marketplace $integration): Collection {
        // Initialize Inventory Query
        $query = Inventory::select(Inventory::getTableName().'.*')
                          ->where('dealer_id', '=', $integration->dealer_id)
                          ->where('show_on_website', 1)
                          ->where(function(Builder $query) {
                              $query->where('is_archived', 0)
                                    ->orWhereNull('is_archived');
                          })
                          ->where(function(Builder $query) {
                              $query->where(function(Builder $query) {
                                  $query->where(Inventory::getTableName().'.status', '<>', 2)
                                        ->where(Inventory::getTableName().'.status', '<>', 6);
                              })->orWhereNull(Inventory::getTableName().'.status');
                          });

        // Append Join
        $query = $query->leftJoin(Listings::getTableName(), function($join) use($integration) {
            $join->on(Listings::getTableName() . '.inventory_id', '=',
                        Inventory::getTableName() . '.inventory_id')
                 ->where(Listings::getTableName().'.username', '=', $integration->fb_username)
                 ->where(Listings::getTableName().'.page_id', '=', $integration->page_id);
        })->whereNull(Listings::getTableName() . '.facebook_id');

        // Append Filters
        if (!empty($integration->filter_map)) {
            $query = $query->where(function(Builder $query) use($integration) {
                foreach($integration->filter_map as $type => $values) {
                    $query = $query->orWhereIn(Filter::FILTER_COLUMNS[$type], $values);
                }
            });
        }

        // Get All Listings
        return $query->get();
    }
}
