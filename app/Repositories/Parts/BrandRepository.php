<?php

namespace App\Repositories\Parts;

use App\Repositories\Parts\BrandRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\Brand;
use App\Models\Parts\Part;

/**
 *
 * @author Eczek
 */
class BrandRepository implements BrandRepositoryInterface {

    private $sortOrders = [
        'name' => [
            'field' => 'name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'name',
            'direction' => 'ASC'
        ],
    ];

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {

        if (isset($params['dealer_id'])) {
            $query = Part::with('brand');
            $query = $query->whereIn('dealer_id', $params['dealer_id'])
                    ->whereNotNull('brand_id')
                    ->groupBy('parts_v1.brand_id');
        } else {
            $query = Brand::where('id', '>', 0);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }


        if (isset($params['name']) && !isset($params['dealer_id'])) {
            $query = $query->where('name', 'like', '%'.$params['name'].'%');
        } else if (isset($params['name'])) {
            $query = $query->whereHas('brand', function($q) use ($params) {
                $q->where('name', 'like', '%'.$params['name'].'%');
            });
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
