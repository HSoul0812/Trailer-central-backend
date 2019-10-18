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
        throw new NotImplementedException;
    }

    public function getAll($params) {
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        if (isset($params['name'])) {
            $query = $query->where('name', 'like', '%'.$params['name'].'%');
        }
        
        return $query::paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
