<?php

namespace App\Repositories\Parts;

use App\Repositories\Parts\CostModifierRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Parts\CostModifier;

class CostModifierRepository implements CostModifierRepositoryInterface {
    
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
        throw new NotImplementedException;
    }

    public function getByDealerId($dealerId) {
        return CostModifier::where('dealer_id', $dealerId)->first();
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
