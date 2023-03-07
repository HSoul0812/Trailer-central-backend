<?php

namespace App\Repositories\Marketing\Craigslist;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\ClCity;
use App\Repositories\Traits\SortTrait;

class CityRepository implements CityRepositoryInterface {

    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'code' => [
            'field' => 'code',
            'direction' => 'DESC'
        ],
        '-code' => [
            'field' => 'code',
            'direction' => 'ASC'
        ],
        'name' => [
            'field' => 'name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'name',
            'direction' => 'ASC'
        ],
        'domain' => [
            'field' => 'domain',
            'direction' => 'DESC'
        ],
        '-domain' => [
            'field' => 'domain',
            'direction' => 'ASC'
        ],
        'timezone' => [
            'field' => 'timezone',
            'direction' => 'DESC'
        ],
        '-timezone' => [
            'field' => 'timezone',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create ClCity
     * 
     * @param array $params
     * @return ClCity
     */
    public function create($params) {
        // Create ClCity
        return ClCity::create($params);
    }

    /**
     * Delete ClCity
     * 
     * @param string $code
     * @throws NotImplementedException
     */
    public function delete($code) {
        throw NotImplementedException;
    }

    /**
     * Get ClCity
     * 
     * @param array $params
     * @return ClCity
     */
    public function get($params) {
        // Name Exists?
        if(isset($params['name']) && $params['name']) {
            return ClCity::where('name', $params['name'])->firstOrFail();
        }

        // Code Exists?
        if(isset($params['code']) && $params['code']) {
            // Find ClCity By Code
            return ClCity::findOrFail($params['code']);
        }

        // Find ClCity By ID (Still Must Be Code)
        return ClCity::findOrFail($params['id']);
    }

    /**
     * Get All ClCity That Match Params
     * 
     * @param array $params
     * @return Collection<ClCity>
     */
    public function getAll($params) {
        $query = ClCity::where('id', '>', 0);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 1000;
        }

        if (isset($params['timezone'])) {
            $query = $query->where('timezone', $params['timezone']);
        }

        if (isset($params['country']) && in_array($params['country'], ClCity::COUNTRY_CODES)) {
            $query = $query->where('alt_name', 'LIKE', '%, ' . strtolower($params['country']));
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('code', $params['id']);
        }

        if (isset($params['code'])) {
            $query = $query->whereIn('code', $params['code']);
        }

        if(!isset($params['sort'])) {
            $params['sort'] = '-name';
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Update ClCity
     * 
     * @param array $params
     * @return ClCity
     */
    public function update($params) {
        $city = $this->get($params);

        DB::transaction(function() use (&$city, $params) {
            // Fill ClCity Details
            $city->fill($params)->save();
        });

        return $city;
    }

    /**
     * Create OR Update ClCity
     * 
     * @param array $params
     * @return ClCity
     */
    public function createOrUpdate(array $params): ClCity {
        // Get Post
        $city = $this->get($params);

        // Post Exists? Update!
        if(!empty($city->code)) {
            return $this->update($params);
        }

        // Create Instead
        return $this->create($params);
    }


    protected function getSortOrders() {
        return $this->sortOrders;
    }
}