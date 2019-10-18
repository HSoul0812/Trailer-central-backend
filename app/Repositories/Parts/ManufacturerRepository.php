<?php

namespace App\Repositories\Parts;

use App\Repositories\Parts\BrandRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\Manufacturer;
use App\Models\Parts\Part;

/**
 *  
 * @author Eczek
 */
class ManufacturerRepository implements ManufacturerRepositoryInterface {


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
            $query = Part::with('manufacturer');
            $query = $query->whereIn('dealer_id', $params['dealer_id'])
                    ->whereNotNull('manufacturer_id')
                    ->groupBy('parts_v1.manufacturer_id');
        } else {
            $query = Manufacturer::with('parts'); 
        }        

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }        
                
        if (isset($params['name']) && !isset($params['dealer_id'])) {
            $query = $query->where('name', 'like', '%'.$params['name'].'%');
        } else if (isset($params['name'])) {
            $query = $query->whereHas('manufacturer', function($q) use ($params) {
                $q->where('name', 'like', '%'.$params['name'].'%');
            });
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
