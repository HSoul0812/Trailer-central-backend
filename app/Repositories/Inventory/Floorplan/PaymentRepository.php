<?php

namespace App\Repositories\Inventory\Floorplan;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Floorplan\Payment;
use App\Repositories\Repository;

/**
 *  
 * @author Marcel
 */
class PaymentRepository implements PaymentRepositoryInterface {

    private $sortOrders = [
        'type' => [ 
            'field' => 'type',
            'direction' => 'DESC'
        ],
        '-type' => [
            'field' => 'type',
            'direction' => 'ASC'
        ],
        'payment_type' => [
            'field' => 'payment_type',
            'direction' => 'DESC'
        ],
        '-payment_type' => [
            'field' => 'payment_type',
            'direction' => 'ASC'
        ],
        'amount' => [
            'field' => 'amount',
            'direction' => 'DESC'
        ],
        '-amount' => [
            'field' => 'amount',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ]
    ];

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
        $query = Payment::with('inventory')
            ->whereHas('inventory', function($q) use($params) {
                $q->where('dealer_id', '=', $params['dealer_id']);
            });
        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('type', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('payment_type', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('amount', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhereHas('inventory', function($q) use($params) {
                        $q->where('title', 'LIKE', '%' . $params['search_term'] . '%');
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
