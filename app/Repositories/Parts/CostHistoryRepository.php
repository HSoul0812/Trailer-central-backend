<?php

namespace App\Repositories\Parts;

use App\Models\Parts\CostHistory;
use App\Exceptions\NotImplementedException;
use App\Repositories\Traits\SortTrait;

/**
 * Part Cost History Repository
 *
 * @author Marcel
 */
class CostHistoryRepository implements CostHistoryRepositoryInterface {

    use SortTrait;

    public function create($params) {
        return CostHistory::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException();
    }

    public function get($params) {
        return CostHistory::findOrFail($params['id']);
    }

    public function getAll($params)
    {
        throw new NotImplementedException();
    }

    public function update($params) {
        throw new NotImplementedException();
    }
}
