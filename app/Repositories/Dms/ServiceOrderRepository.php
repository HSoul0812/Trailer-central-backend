<?php

namespace App\Repositories\Dms;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\ServiceOrder;

/**
 * @author Marcel
 */
class ServiceOrderRepository implements ServiceOrderRepositoryInterface {

    /**
     * @var ServiceOrder
     */
    protected $model;

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

    public function __construct(ServiceOrder $serviceOrder) {
        $this->model = $serviceOrder;
    }


    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return $this->model->findOrFail($params['id']);
    }

    public function getAll($params) {
        if (isset($params['dealer_id'])) {
            $query = $this->model->where('dealer_id', '=', $params['dealer_id']);
        } else {
            $query = $this->model->where('id', '>', 0);
        }
        // Filter out service orders which location doesn't exist
        $query = $query->where('location', '>', 0);
        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('user_defined_id', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('total_price', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $params['search_term'] . '%')
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
                case ServiceOrder::TYPE_ESTIMATE:
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
                case ServiceOrder::SERVICE_ORDER_NOT_COMPLETED:
                    $query = $query->whereNotIn('status', ['picked_up', 'ready_for_pickup']);
                    break;
            }
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        if (isset($params['created_at_or_closed_at_lte'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('created_at', '<=', $params['created_at_or_closed_at_lte'])
                    ->orWhere('closed_at', '<=', $params['created_at_or_closed_at_lte']);
            });
        }

        if (isset($params['created_at_or_closed_at_gte'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('created_at', '>=', $params['created_at_or_closed_at_gte'])
                    ->orWhere('closed_at', '>=', $params['created_at_or_closed_at_gte']);
            });
        }

        if (isset($params['date_in_or_date_out_lte'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('date_in', '<=', $params['date_in_or_date_out_lte'] . ' 23:59:59')
                    ->orWhere('date_out', '<=', $params['date_in_or_date_out_lte'] . ' 23:59:59');
            });
        }

        if (isset($params['date_in_or_date_out_gte'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('date_in', '>=', $params['date_in_or_date_out_gte'] . ' 00:00:00')
                    ->orWhere('date_out', '>=', $params['date_in_or_date_out_gte'] . ' 00:00:00');
            });
        }

        if (isset($params['inventory_ids']) && is_array($params['inventory_ids'])) {
            $query = $query->whereIn('inventory_id', $params['inventory_ids']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $serviceOrder = $this->get($params);

        $serviceOrder->fill($params);
        $serviceOrder->save();

        return $serviceOrder;
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }
        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

}
