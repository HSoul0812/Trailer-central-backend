<?php

namespace App\Repositories\Dms;

use Illuminate\Support\Facades\DB;
use App\Repositories\Dms\ServiceOrderRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\ServiceOrder;
use App\Models\CRM\Account\Payment;

/**
 * @author Marcel
 */
class ServiceOrderRepository implements ServiceOrderRepositoryInterface {

    private $sortOrders = [
        'user_defined_id' => [ 
            'field' => 'user_defined_id',
            'direction' => 'DESC'
        ],
        '-user_defined_id' => [
            'field' => 'user_defined_id',
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
        'closed_at' => [
            'field' => 'closed_at',
            'direction' => 'DESC'
        ],
        '-closed_at' => [
            'field' => 'closed_at',
            'direction' => 'ASC'
        ],
        'total_price' => [
            'field' => 'total_price',
            'direction' => 'DESC'
        ],
        '-total_price' => [
            'field' => 'total_price',
            'direction' => 'ASC'
        ],
        'status' => [
            'field' => 'status',
            'direction' => 'DESC'
        ],
        '-status' => [
            'field' => 'status',
            'direction' => 'ASC'
        ],
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
        if (isset($params['dealer_id'])) {
            $query = ServiceOrder::where('dealer_id', '=', $params['dealer_id']);
        } else {
            $query = ServiceOrder::where('id', '>', 0);  
        }
        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('user_defined_id', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('total_price', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhereHas('customer', function($q) use($params) {
                        $q->where('display_name', 'LIKE', '%' . $params['search_term'] . '%');
                    });
            });
        }
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        if (isset($params['status'])) {
            switch ($params['status']) {
                case ServiceOrder::SERVICE_ORDER_ESTIMATE:
                    $query = $query
                        ->where('type', '=', 'estimate')
                        ->whereNotIn('status', ['picked_up', 'ready_for_pickup']);
                    break;
                case ServiceOrder::SERVICE_ORDER_SCHEDULED:
                    $query = $query
                        ->where('type', '<>', 'estimate')
                        ->whereNotIn('status', ['picked_up', 'ready_for_pickup']);
                    break;
                case ServiceOrder::SERVICE_ORDER_COMPLETED:
                    $query = $query->whereIn('status', ['picked_up', 'ready_for_pickup']);
                    break;
            }
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
