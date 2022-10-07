<?php

namespace App\Repositories\Integration\Facebook;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Integration\Facebook\Feed;
use App\Repositories\Traits\SortTrait;

class FeedRepository implements FeedRepositoryInterface {
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
     * Create Facebook Feed
     * 
     * @param array $params
     * @return Feed
     */
    public function create($params) {
        // Active Not Set?
        if(!isset($params['is_active'])) {
            $params['is_active'] = 1;
        }

        // Filters Cannot be null
        if(empty($params['filters'])) {
            $params['filters'] = '';
        }

        // Feed Feed Already Exists?
        return Feed::create($params);
    }

    /**
     * Delete Feed
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        // Delete Feed
        return Feed::findOrFail($id)->delete();
    }

    /**
     * Get Feed
     * 
     * @param array $params
     * @return Feed
     */
    public function get($params) {
        // Find Feed By ID
        return Feed::findOrFail($params['id']);
    }

    /**
     * Get All Feeds That Match Params
     * 
     * @param array $params
     * @return Collection of Feeds
     */
    public function getAll($params) {
        $query = Feed::select();
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['business_id'])) {
            $query = $query->where('business_id', $params['business_id']);
        }

        if (isset($params['catalog_id'])) {
            $query = $query->where('catalog_id', $params['catalog_id']);
        }

        if (isset($params['feed_id'])) {
            $query = $query->where('feed_id', $params['feed_id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update Feed
     * 
     * @param array $params
     * @return Feed
     */
    public function update($params) {
        $feed = Feed::findOrFail($params['id']);

        DB::transaction(function() use (&$feed, $params) {
            // Fill Feed Details
            $feed->fill($params)->save();
        });

        return $feed;
    }

    /**
     * Create or Update Feed
     * 
     * @param array $params
     * @return Feed
     */
    public function createOrUpdate($params) {
        // Feed Already Exists?
        $feed = Feed::where('catalog_id', $params['catalog_id'])->first();
        if(!empty($feed->id)) {
            // Update Feed
            $params['id'] = $feed->id;
            return $this->update($params);
        }

        // Create Feed
        return $this->create($params);
    }


    protected function getSortOrders() {
        return $this->sortOrders;
    }


    /**
     * Get Feed Path
     * 
     * @param int $businessId
     * @param int $catalogId
     * @param bool $remote
     * @return string of calculated feed path
     */
    public function getFeedUrl($businessId, $catalogId, $remote = true)
    {
        // Set URL
        $url = $remote ? config('filesystems.disks.s3.url') : '';

        // Return URL
        return $url . '/' . Feed::CATALOG_URL_PREFIX . '/' . $businessId . '/' . $catalogId . '.csv';
    }

    /**
     * Get Feed Name
     * 
     * @param int $catalogId
     * @return string of calculated feed name
     */
    public function getFeedName($catalogId)
    {
        return "Feed for Catalog #" . $catalogId;
    }
}
