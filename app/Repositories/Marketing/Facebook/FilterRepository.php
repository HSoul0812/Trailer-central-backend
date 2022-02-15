<?php

namespace App\Repositories\Marketing\Facebook;

use App\Exceptions\NotImplementedException;
use App\Models\Marketing\Facebook\Filter;
use App\Repositories\Traits\SortTrait;
use Illuminate\Support\Facades\DB;

class FilterRepository implements FilterRepositoryInterface {
    use SortTrait;

    /**
     * Define Sort Orders
     *
     * @var array
     */
    private $sortOrders = [
        'type' => [
            'field' => 'filter_type',
            'direction' => 'DESC'
        ],
        '-type' => [
            'field' => 'filter_type',
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
     * Create Facebook Filter
     * 
     * @param array $params
     * @return Filter
     */
    public function create($params) {
        // Create Filter
        return Filter::create($params);
    }

    /**
     * Delete Filter
     * 
     * @param int $id
     * @throws NotImplementedException
     */
    public function delete($id) {
        // Delete Filter
        return Filter::findOrFail($id)->delete();
    }

    /**
     * Get Filter
     * 
     * @param array $params
     * @return Filter
     */
    public function get($params) {
        // Find Filter By ID
        return Filter::findOrFail($params['id']);
    }

    /**
     * Get All Filters That Match Params
     * 
     * @param array $params
     * @return Collection of Filters
     */
    public function getAll($params) {
        $query = Filter::where('marketplace_id', '=', $params['marketplace_id']);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['type'])) {
            $query = $query->where('filter_type', $params['type']);
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
     * Update Filter
     * 
     * @param array $params
     * @return Filter
     */
    public function update($params) {
        $filter = Filter::findOrFail($params['id']);

        DB::transaction(function() use (&$filter, $params) {
            // Fill Filter Details
            $filter->fill($params)->save();
        });

        return $filter;
    }

    /**
     * Delete All Filters By Marketplace ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteAll(int $id): bool {
        // Delete All Filters By Marketplace ID
        return Filter::where('marketplace_id', $id)->delete();
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }
}
