<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Quickbooks\ItemNew;

/**
 * @author Marcel
 */
class ItemNewRepository implements ItemNewRepositoryInterface {

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        if (isset($params['item_id'])) {
            return ItemNew::findOrFail($params['item_id']);
        }

        $query = ItemNew::where('id', '>', 0);
        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }
        if (isset($params['name'])) {
            $query = $query->where('name', $params['name']);
        }
        if (isset($params['is_default'])) {
            $query = $query->where('is_default', $params['is_default']);
        }

        return $query->first();
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
