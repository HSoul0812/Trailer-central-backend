<?php

namespace App\Repositories\Parts;

use App\Repositories\Parts\TypeRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\Type;
use App\Models\Parts\Part;

/**
 *  
 * @author Eczek
 */
class TypeRepository implements TypeRepositoryInterface {


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
            $query = Part::with('type');
            $query = $query->whereIn('dealer_id', $params['dealer_id'])
                    ->whereNotNull('type_id')
                    ->groupBy('parts_v1.type_id');
        } else {
            $query = Type::with('parts');
        }        

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }        
                
        if (isset($params['name']) && !isset($params['dealer_id'])) {
            $query = $query->where('name', 'like', '%'.$params['name'].'%');
        } else if (isset($params['name'])) {
            $query = $query->whereHas('type', function($q) use ($params) {
                $q->where('name', 'like', '%'.$params['name'].'%');
            });
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
