<?php

namespace App\Repositories\Parts;

use App\Exceptions\NotImplementedException;
use App\Models\Parts\Category;
use App\Models\Parts\Part;
use InvalidArgumentException;

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

    /**
     * Retrieves the desired part or the first part for the dealer.
     * If the provided part id was not found ir will fail
     *
     * @param array $params
     * @return mixed
     */
    public function get($params)
    {
        if (isset($params['id'])) {
            return Category::findOrFail($params['id']);
        }

        throw new InvalidArgumentException('Parameter "id" is required');
    }

    public function getAll($params) {

        if (isset($params['dealer_id'])) {
            $query = Part::with('category');
            $query = $query->whereIn('dealer_id', $params['dealer_id'])
                    ->whereNotNull('category_id')
                    ->groupBy('parts_v1.category_id');
        } else {
            $query = Category::where('id', '>', 0);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }


        if (isset($params['name']) && !isset($params['dealer_id'])) {
            $query = $query->where('name', 'like', '%'.$params['name'].'%');
        } else if (isset($params['name'])) {
            $query = $query->whereHas('category', function($q) use ($params) {
                $q->where('name', 'like', '%'.$params['name'].'%');
            });
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
