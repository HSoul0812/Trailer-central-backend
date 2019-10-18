<?php

namespace App\Repositories\Parts;

use App\Repositories\Parts\CategoryRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\Category;

/**
 *  
 * @author Eczek
 */
class CategoryRepository implements CategoryRepositoryInterface {


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
        $query = Category::with('parts');
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        if (isset($params['dealer_id'])) {
            $query = $query->whereHas('parts', function($q) use ($params) {
                $q->whereIn('dealer_id', $params['dealer_id']);
            });
        }
        
        if (isset($params['name'])) {
            $query = $query->where('name', 'like', '%'.$params['name'].'%');
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
