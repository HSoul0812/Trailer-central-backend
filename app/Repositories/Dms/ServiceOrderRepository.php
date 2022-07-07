<?php

namespace App\Repositories\Dms;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Dms\ServiceOrder;
use Illuminate\Support\Facades\DB;

/**
 * @author Marcel
 */
class ServiceOrderRepository implements ServiceOrderRepositoryInterface
{
    private const SERVICE_ORDER_STATUS = [
        ServiceOrder::STATUS_PICKED_UP,
        ServiceOrder::STATUS_READY_FOR_PICK_UP,
        ServiceOrder::STATUS_ONLY_READY_FOR_PICK_UP,
    ];

    private const SORT_ORDERS = [
        'user_defined_id' => [
            'field' => 'user_defined_id',
            'direction' => 'DESC',
        ],
        '-user_defined_id' => [
            'field' => 'user_defined_id',
            'direction' => 'ASC',
        ],
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC',
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC',
        ],
        'closed_at' => [
            'field' => 'closed_at',
            'direction' => 'DESC',
        ],
        '-closed_at' => [
            'field' => 'closed_at',
            'direction' => 'ASC',
        ],
        'total_price' => [
            'field' => 'total_price',
            'direction' => 'DESC',
        ],
        '-total_price' => [
            'field' => 'total_price',
            'direction' => 'ASC',
        ],
        'status' => [
            'field' => 'status',
            'direction' => 'DESC',
        ],
        '-status' => [
            'field' => 'status',
            'direction' => 'ASC',
        ],
        'paid_amount' => [
            'field' => 'total_paid_amount',
            'direction' => 'DESC',
        ],
        '-paid_amount' => [
            'field' => 'total_paid_amount',
            'direction' => 'ASC',
        ],
    ];

    /**
     * @var ServiceOrder
     */
    protected $model;

    public function __construct(ServiceOrder $serviceOrder)
    {
        $this->model = $serviceOrder;
    }

    public function create($params)
    {
        throw new NotImplementedException;
    }

    public function delete($params)
    {
        throw new NotImplementedException;
    }

    public function get($params)
    {
        return $this->model->findOrFail($params['id']);
    }

    public function getAll($params)
    {
        if (isset($params['dealer_id'])) {
            $query = $this->model->where('dealer_id', '=', $params['dealer_id']);
        } else {
            $query = $this->model->where('id', '>', 0);
        }
        // Filter out service orders which location doesn't exist

        if (isset($params['location']) && $params['location'] > 0) {
            $query = $query->where('location', '=', $params['location']);
        } else {
            $query = $query->where('location', '>', 0);
        }

        if (isset($params['search_term'])) {
            $query = $query->where(function ($q) use ($params) {
                $q->where('user_defined_id', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('total_price', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhereHas('customer', function ($q) use ($params) {
                        $q->where('display_name', 'LIKE', '%' . $params['search_term'] . '%');
                    })
                    ->orWhereHas('inventory', function ($q) use ($params) {
                        $q->where('vin', 'LIKE', '%' . $params['search_term'] . '%');
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
                        ->whereNotIn('status', self::SERVICE_ORDER_STATUS);

                    break;
                case ServiceOrder::SERVICE_ORDER_SCHEDULED:
                    $query = $query
                        ->where('type', '<>', 'estimate')
                        ->whereNotIn('status', self::SERVICE_ORDER_STATUS);

                    break;
                case ServiceOrder::SERVICE_ORDER_COMPLETED:
                    $query = $query->whereIn('status', self::SERVICE_ORDER_STATUS);

                    break;
                case ServiceOrder::SERVICE_ORDER_NOT_COMPLETED:
                    $query = $query->whereNotIn('status', self::SERVICE_ORDER_STATUS);

                    break;
            }
        }

        if (!empty($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        if (!empty($params['created_at_or_closed_at_lte'])) {
            $query = $query->where(function ($q) use ($params) {
                $q->where('created_at', '<=', $params['created_at_or_closed_at_lte'])
                    ->orWhere('closed_at', '<=', $params['created_at_or_closed_at_lte']);
            });
        }

        if (!empty($params['created_at_or_closed_at_gte'])) {
            $query = $query->where(function ($q) use ($params) {
                $q->where('created_at', '>=', $params['created_at_or_closed_at_gte'])
                    ->orWhere('closed_at', '>=', $params['created_at_or_closed_at_gte']);
            });
        }

        if (!empty($params['date_in_or_date_out_lte'])) {
            $query = $query->where(function ($q) use ($params) {
                $q->where('date_in', '<=', $params['date_in_or_date_out_lte'] . ' 23:59:59')
                    ->orWhere('date_out', '<=', $params['date_in_or_date_out_lte'] . ' 23:59:59');
            });
        }

        if (!empty($params['date_in_or_date_out_gte'])) {
            $query = $query->where(function ($q) use ($params) {
                $q->where('date_in', '>=', $params['date_in_or_date_out_gte'] . ' 00:00:00')
                    ->orWhere('date_out', '>=', $params['date_in_or_date_out_gte'] . ' 00:00:00');
            });
        }

        if (!empty($params['inventory_ids']) && is_array($params['inventory_ids'])) {
            $query = $query->where(function ($q) use ($params) {
                $q->whereIn('inventory_id', $params['inventory_ids'])
                    ->where('inventory_id', '!=', 0);
            });
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params)
    {
        $serviceOrder = $this->get($params);

        // Adds additional field to mark it as completed in params.
        if (in_array($params['status'], ServiceOrder::COMPLETED_ORDER_STATUS)) {
            $params['closed_at'] = now()->format('Y-m-d H:i:s');
        }

        $serviceOrder->fill($params);
        $serviceOrder->save();

        return $serviceOrder;
    }

    private function addSortQuery($query, $sort)
    {
        if (empty(self::SORT_ORDERS[$sort])) {
            return;
        }

        $sortOrder = self::SORT_ORDERS[$sort];

        if ($sortOrder['field'] === 'total_paid_amount') {
            $groupedPayments = Payment::select('repair_order_id', DB::raw('SUM(amount) as paid_amount, qb_invoices.po_no as po_no, qb_invoices.po_amount as po_amount'))
                ->leftJoin('qb_invoices', 'qb_payment.invoice_id', '=', 'qb_invoices.id')
                ->groupBy('qb_invoices.repair_order_id');

            $query = $query->leftJoinSub($groupedPayments, 'invoice', function ($join) {
                $join->on('dms_repair_order.id', '=', 'invoice.repair_order_id');
            });

            $query->select('*', DB::raw('
                IF(invoice.po_no AND NOT closed_by_related_unit_sale,
                invoice.paid_amount + invoice.po_amount,
                (SELECT CASE WHEN closed_by_related_unit_sale THEN total_price ELSE invoice.paid_amount END)) AS total_paid_amount'));
        }

        return $query->orderBy($sortOrder['field'], $sortOrder['direction']);
    }
}
