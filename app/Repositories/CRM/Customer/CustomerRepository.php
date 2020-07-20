<?php

namespace App\Repositories\CRM\Customer;

use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\User\Customer;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements CustomerRepositoryInterface 
{
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
        throw NotImplementedException;
    }

    public function update($params) {
        throw NotImplementedException;
    }

    public function getCustomersWihOpenBalance($dealerId, $perPage = 15) {
         $query = Customer::where('dealer_id', $dealerId)->has('openQuotes');
         return $query->get();           
    }

}
