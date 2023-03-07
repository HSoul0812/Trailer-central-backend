<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Repositories\Dms\Quickbooks\AccountRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Quickbooks\Account;

/**
 * @author Marcel
 */
class AccountRepository implements AccountRepositoryInterface {

    private $sortOrders = [
        'name' => [
            'field' => 'name',
            'direction' => 'DESC'
        ],
        '-name' => [
            'field' => 'name',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'type' => [
            'field' => 'type',
            'direction' => 'DESC'
        ],
        '-type' => [
            'field' => 'type',
            'direction' => 'ASC'
        ],
    ];


    public function create($params) {
        return Account::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        if (isset($params['account_id'])) {
            return Account::findOrFail($params['account_id']);
        }

        $query = Account::where('id', '>', 0);
        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }
        if (isset($params['name'])) {
            $query = $query->where('name', $params['name']);
        }
        if (isset($params['type'])) {
            $query = $query->where('type', $params['type']);
        }

        return $query->first();
    }

    public function getAll($params) {
        if (isset($params['dealer_id'])) {
            $query = Account::where('dealer_id', '=', $params['dealer_id']);
        } else {
            $query = Account::where('id', '>', 0);
        }
        if (isset($params['type'])) {
            $query = $query->whereIn('type', $params['type']);
        }
        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('name', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('sub_type', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhereHas('parent', function($q) use($params) {
                        $q->where('name', 'LIKE', '%' . $params['search_term'] . '%');
                    });
            });
        }
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }
        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}
