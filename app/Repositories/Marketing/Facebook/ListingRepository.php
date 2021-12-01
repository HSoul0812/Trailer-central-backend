<?php

namespace App\Repositories\Marketing\Facebook;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Facebook\Listings;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

class ListingRepository implements ListingRepositoryInterface {
    use SortTrait;

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
}
