<?php

namespace App\Repositories\CRM\User;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\NewDealerUser;

class SalesPersonRepository implements SalesPersonRepositoryInterface {
    
    public function create($params) {
        throw NotImplementedException;
    }

    public function delete($params) {
        throw NotImplementedException;
    }

    public function get($params) {
        throw NotImplementedException;
    }

    public function getAll($params) {
        $query = SalesPerson::select('*');
        
        if (isset($params['dealer_id'])) {
            $newDealerUser = NewDealerUser::findOrFail($params['dealer_id']);
            $query = $query->where('user_id', $newDealerUser->user_id);
        }
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw NotImplementedException;
    }

}
