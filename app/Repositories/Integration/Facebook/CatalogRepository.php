<?php

namespace App\Repositories\Integration\Facebook;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Integration\Facebook\Catalog;
use App\Repositories\Traits\SortTrait;

class CatalogRepository implements CatalogRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'location' => [
            'field' => 'dealer_location_id',
            'direction' => 'DESC'
        ],
        '-location' => [
            'field' => 'dealer_location_id',
            'direction' => 'ASC'
        ],
        'account_name' => [
            'field' => 'account_name',
            'direction' => 'DESC'
        ],
        '-account_name' => [
            'field' => 'account_name',
            'direction' => 'ASC'
        ],
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
     * Create Facebook Catalog
     * 
     * @param array $params
     * @return Catalog
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

        // Create Catalog
        return Catalog::create($params);
    }

    /**
     * Delete Catalog
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        // Delete Catalog
        return Catalog::findOrFail($id)->delete();
    }

    /**
     * Get Catalog
     * 
     * @param array $params
     * @return Catalog
     */
    public function get($params) {
        // Find Catalog By ID
        return Catalog::findOrFail($params['id']);
    }

    /**
     * Get All Catalogs That Match Params
     * 
     * @param array $params
     * @return Collection of Catalogs
     */
    public function getAll($params) {
        $query = Catalog::where('dealer_id', '=', $params['dealer_id'])
            ->whereHas('accessToken')
            ->whereHas('page');

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['account_id'])) {
            $query = $query->where('account_id', $params['account_id']);
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
     * Update Catalog
     * 
     * @param array $params
     * @return Catalog
     */
    public function update($params) {
        $catalog = Catalog::findOrFail($params['id']);

        DB::transaction(function() use (&$catalog, $params) {
            // Fill Catalog Details
            $catalog->fill($params)->save();
        });

        return $catalog;
    }

    /**
     * Find Catalogs
     * 
     * @param array $params
     * @return Collection of Catalogs
     */
    public function find($params) {
        $query = Catalog::where('id', '>', 0);

        if (isset($params['business_id'])) {
            $query = $query->where('business_id', $params['business_id']);
        }

        if (isset($params['account_id'])) {
            $query = $query->where('account_id', $params['account_id']);
        }

        if (isset($params['catalog_id'])) {
            $query = $query->where('catalog_id', $params['catalog_id']);
        }
        
        return $query->get();
    }

    /**
     * Find One Catalog
     * 
     * @param array $params
     * @return Catalog
     */
    public function findOne($params) {
        return $this->find($params)->first();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
