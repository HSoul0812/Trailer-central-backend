<?php

namespace App\Repositories\Inventory\Floorplan;

use App\Repositories\Inventory\Floorplan\VendorRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Floorplan\Vendor;

class VendorRepository implements VendorRepositoryInterface 
{
    
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
        $query = Vendor::where('show_on_floorplan', 1);
         
        if (isset($params['dealer_id'])) {
            $query->where('dealer_id', $params['dealer_id']);
        }
        if (isset($params['search_term'])) {
            $query->where('name', 'like', '%'.$params['search_term'].'%');
        }
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
