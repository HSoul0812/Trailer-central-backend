<?php

namespace App\Repositories\User;

use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\User\DealerLocation;

class DealerLocationRepository implements DealerLocationRepositoryInterface {
    
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
        $query = DealerLocation::select('*');
        
        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
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
