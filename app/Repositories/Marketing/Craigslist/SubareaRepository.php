<?php

namespace App\Repositories\Marketing\Craigslist;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Craigslist\Subarea;
use App\Repositories\Traits\SortTrait;

class SubareaRepository implements SubareaRepositoryInterface {

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
        'city_code' => [
            'field' => 'city_code',
            'direction' => 'DESC'
        ],
        '-city_code' => [
            'field' => 'city_code',
            'direction' => 'ASC'
        ],
        'name' => [
            'field' => 'name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'name',
            'direction' => 'ASC'
        ]
    ];

    /**
     * Create Subarea
     * 
     * @param array $params
     * @return Subarea
     */
    public function create($params) {
        // Create Subarea
        return Subarea::create($params);
    }

    /**
     * Delete Subarea
     * 
     * @param string $code
     * @throws NotImplementedException
     */
    public function delete($code) {
        throw NotImplementedException;
    }

    /**
     * Get Subarea
     * 
     * @param array $params
     * @return Subarea
     */
    public function get($params) {
        // Name Exists?
        if(isset($params['name']) && $params['name']) {
            return Subarea::where('name', $params['name'])->firstOrFail();
        }

        // Code Exists?
        if(isset($params['code']) && $params['code']) {
            // Find Subarea By Code
            return Subarea::findOrFail($params['code']);
        }

        // Find Subarea By ID (Still Must Be Code)
        return Subarea::findOrFail($params['id']);
    }

    /**
     * Get All Subarea That Match Params
     * 
     * @param array $params
     * @return Collection<Subarea>
     */
    public function getAll($params) {
        $query = Subarea::where('id', '>', 0);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 1000;
        }

        if (isset($params['city_code'])) {
            $query = $query->where('city_code', $params['city_code']);
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
     * Update Subarea
     * 
     * @param array $params
     * @return Subarea
     */
    public function update($params) {
        $city = $this->get($params);

        DB::transaction(function() use (&$city, $params) {
            // Fill Subarea Details
            $city->fill($params)->save();
        });

        return $city;
    }


    /**
     * Find Subarea
     * 
     * @param array $params
     * @return null|Subarea
     */
    public function find(array $params): ?Subarea {
        // Name Exists?
        if(isset($params['name']) && $params['name']) {
            return Subarea::where('name', $params['name'])->first();
        }

        // Code Exists?
        if(isset($params['code']) && $params['code']) {
            // Find Subarea By Code
            return Subarea::find($params['code']);
        }

        // Find Subarea By ID (Still Must Be Code)
        return Subarea::find($params['id']);
    }

    /**
     * Create OR Update Subarea
     * 
     * @param array $params
     * @return Subarea
     */
    public function createOrUpdate(array $params): Subarea {
        // Get Subarea
        $city = $this->find($params);

        // Subarea Exists? Update!
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