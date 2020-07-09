<?php

namespace App\Repositories\CRM\Customer;

use App\Repositories\Repository;

interface CustomerRepositoryInterface extends Repository {
    
    public function getCustomersWihOpenBalance($dealerId, $perPage = 15);
    
}
