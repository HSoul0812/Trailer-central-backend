<?php

namespace App\Repositories\Parts;

use App\Repositories\Parts\VendorRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\Vendor;
use App\Models\Parts\Part;

/**
 *  
 * @author Eczek
 */
class VendorRepository implements VendorRepositoryInterface {


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
            $query = Part::with('vendor');
            $query = $query->whereIn('dealer_id', $params['dealer_id'])
                    ->whereNotNull('vendor_id')
                    ->groupBy('parts_v1.vendor_id');
        } else {
            $query = Vendor::where('id', '>', 0);
        }        

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        if (isset($params['show_on_part'])) {
            $query = $query->whereHas('vendor', function($q) use ($params) {
                $q->where('show_on_part', $params['show_on_part']);
            });
        }
        
        if (isset($params['show_on_inventory'])) {
            $query = $query->whereHas('vendor', function($q) use ($params) {
                $q->where('show_on_inventory', $params['show_on_inventory']);
            });
        }
        
        if (isset($params['show_on_floorplan'])) {
            $query = $query->whereHas('vendor', function($q) use ($params) {
                $q->where('show_on_floorplan', $params['show_on_floorplan']);
            });
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
