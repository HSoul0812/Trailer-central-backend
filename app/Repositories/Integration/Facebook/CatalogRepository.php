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
        // Does User ID Already Exist?
        if(isset($params['user_id'])) {
            $catalog = $this->getByFBId($params);

            // Exists?
            var_dump($catalog);
            var_dump($params);
            if(!empty($catalog->id)) {
                $params['id'] = $catalog->id;
                return $this->update($params);
            }
        }

        // Create Catalog
        return Catalog::create($params);
    }

    /**
     * Delete Catalog
     * 
     * @param array $params
     * @throws NotImplementedException
     */
    public function delete($params) {
        throw new NotImplementedException;
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
     * Get By Facebook User ID
     * 
     * @param array $params
     * @return AccessToken
     */
    public function getByFBId($params) {
        // Find Token By ID
        return Catalog::where('user_id', $params['user_id'])->first();
    }

    /**
     * Get All Catalogs That Match Params
     * 
     * @param array $params
     * @return Collection of Catalogs
     */
    public function getAll($params) {
        $query = Catalog::where('dealer_id', '>', $params['dealer_id']);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('dealer_location_id_id', $params['dealer_location_id']);
        }

        if (isset($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
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

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
