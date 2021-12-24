<?php

namespace App\Repositories\Inventory\Manufacturers;

use App\Repositories\Inventory\Manufacturers\BrandRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Manufacturers\Brand;

class BrandRepository implements BrandRepositoryInterface 
{
    /**
     *
     * @var Brand
     */
    protected $model;

    public function __construct(Brand $model)
    {
        $this->model = $model;
    }
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params): bool {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        $query = $this->model::where('brand_id', '>', 0);

        if (isset($params['search_term'])) {
            $query = $query->where('name', 'LIKE', '%' . $params['search_term'] . '%');
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params): bool {
        throw new NotImplementedException;
    }

}
