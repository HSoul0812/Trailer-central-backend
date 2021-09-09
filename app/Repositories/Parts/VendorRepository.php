<?php

namespace App\Repositories\Parts;

use App\Repositories\Parts\VendorRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\Vendor;

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
        if (isset($params['vendor_id'])) {
            return Vendor::findOrFail($params['vendor_id']);
        }

        $query = Vendor::where('id', '>', 0);
        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }

        return $query->first();
    }

    public function getAll($params) {                
        $isSetDealerId = isset($params['dealer_id']);
        if ($isSetDealerId) {
            $dealers = is_array($params['dealer_id']) ? $params['dealer_id'] : [$params['dealer_id']];
            $query = Vendor::whereIn('dealer_id', $dealers);
            
        } else {
            $query = Vendor::where('id', '>', 0);
        }        

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        if (isset($params['show_on_part'])) {
             $query = $query->where('show_on_part', $params['show_on_part']);
        }
        
        if (isset($params['show_on_inventory'])) {
             $query = $query->where('show_on_inventory', $params['show_on_inventory']);
        }
        
        if (isset($params['show_on_floorplan'])) {
            $query = $query->where('show_on_floorplan', $params['show_on_floorplan']);
        }
                
        if (isset($params['name'])) {
            $query = $query->where('name', 'like', '%'.$params['name'].'%');
        }
        // Do not return deleted vendors
        $query = $query->whereNull('deleted_at');

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
