<?php
namespace App\Repositories\Ecommerce;

use App\Events\Ecommerce\QtyUpdated;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Traits\Repository\Transaction;

class CompletedOrderRepository implements CompletedOrderRepositoryInterface
{
    use Transaction;

    private $sortOrders = [
        'status' => [
            'field' => 'status',
            'direction' => 'ASC'
        ],
        '-status' => [
            'field' => 'status',
            'direction' => 'DESC'
        ],
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        'customer_email' => [
            'field' => 'customer_email',
            'direction' => 'DESC'
        ],
        '-customer_email' => [
            'field' => 'customer_email',
            'direction' => 'DESC'
        ],
    ];

    public function getGrandTotals()
    {
        return [
            'total_all' => $this->getTotalAmount(),
            'total_qty' => $this->getTotalQty(),
        ];
    }

    private function getTotalAmount()
    {
        return CompletedOrder::sum('total_amount');
    }

    private function getTotalQty()
    {
        $completedOrders = CompletedOrder::select('parts')->get();

        $totalQty = 0;
        foreach ($completedOrders as $completedOrder)
        {
            if (!empty($completedOrder['parts'])) {
                $parts = json_decode($completedOrder['parts'], true);
                if ($parts) {
                    array_map(function ($part) use (&$totalQty) { $totalQty += $part['qty']; }, $parts);
                }
            }
        }

        return $totalQty;
    }

    public function getAll($params)
    {
        if (!isset($params['per_page'])) {
          $params['per_page'] = 100;
        }

        $query = CompletedOrder::select('*');

        /**
         * Filters
         */
        $query = $this->addFiltersToQuery($query, $params);

        if (isset($params['sort'])) {
            $query = $query->orderBy($this->sortOrders[$params['sort']]['field'], $this->sortOrders[$params['sort']]['direction']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function create($params): CompletedOrder
    {
        $data = $params['data']['object'];

        $completedOrder = CompletedOrder::where('object_id', $data['id'])->first();

        if (!$completedOrder) {
            $completedOrder = new CompletedOrder();

            $completedOrder->event_id = $params['id'];
            $completedOrder->object_id = $data['id'];
            $completedOrder->parts = $data['parts'];
            $completedOrder->total_amount = $data['amount_total'];
            $completedOrder->payment_status = $data['payment_status'] ?? '';
            $completedOrder->invoice_id = $data['invoice_id'] ?? '';
            $completedOrder->invoice_url = $data['invoice_url'] ?? '';

            $completedOrder->shipping_name = $data['shipto_name'] ?? '';
            $completedOrder->shipping_country = $data['shipto_country'] ?? '';
            $completedOrder->shipping_address = $data['shipto_address'] ?? '';
            $completedOrder->shipping_city = $data['shipto_city'] ?? '';
            $completedOrder->shipping_zip = $data['shipto_postal'] ?? '';
            $completedOrder->shipping_region = $data['shipto_region'] ?? '';

            if (isset($data['no-billing']) && $data['no-billing'] == "1") {
                $completedOrder->billing_name = $data['shipto_name'] ?? '';
                $completedOrder->billing_country = $data['shipto_country'] ?? '';
                $completedOrder->billing_address = $data['shipto_address'] ?? '';
                $completedOrder->billing_city = $data['shipto_city'] ?? '';
                $completedOrder->billing_zip = $data['shipto_postal'] ?? '';
                $completedOrder->billing_region = $data['shipto_region'] ?? '';
            } else {
                $completedOrder->billing_name = $data['billto_name'] ?? '';
                $completedOrder->billing_country = $data['billto_country'] ?? '';
                $completedOrder->billing_address = $data['billto_address'] ?? '';
                $completedOrder->billing_city = $data['billto_city'] ?? '';
                $completedOrder->billing_zip = $data['billto_postal'] ?? '';
                $completedOrder->billing_region = $data['billto_region'] ?? '';
            }
        } else {
            $completedOrder->customer_email = $data['customer_details']['email'];
            $completedOrder->total_amount = $data['amount_total'];
            $completedOrder->payment_method = $data['payment_method_types'][0];
            $completedOrder->stripe_customer = $data['customer'] ?? '';

            $parts = json_decode($completedOrder->parts, true);

            // Dispatch for handle quantity reducing.
            foreach ($parts as $part) {
                QtyUpdated::dispatch($part['id'], $part['qty']);
            }
        }

        $completedOrder->save();

        return $completedOrder;
    }

    public function update($params)
    {
        // TODO: Implement delete() method.
    }

    public function get($params)
    {
        if (isset($params['id'])) {
            return CompletedOrder::findOrFail($params['id']);
        }
    }

    public function delete($params)
    {
        // TODO: Implement delete() method.
    }

    private function addFiltersToQuery($query, $filters, $noStatusJoin = false) {
        if (isset($filters['search_term'])) {
            $query = $this->addSearchToQuery($query, $filters['search_term']);
        }

        if (isset($filters['date_from'])) {
            $query = $this->addDateFromToQuery($query, $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query = $this->addDateToToQuery($query, $filters['date_to']);
        }

        if (isset($filters['status'])) {
            $query = $this->addStatusToQuery($query, $filters['status']);
        }

        return $query;
    }

    private function addDateToToQuery($query, $dateTo) {
        return $query->where(CompletedOrder::getTableName().'.created_at', '<=', $dateTo);
    }

    private function addDateFromToQuery($query, $dateFrom) {
        return $query->where(CompletedOrder::getTableName().'.created_at', '>=', $dateFrom);
    }

    private function addSearchToQuery($query, $search) {;
        return $query->where(function($q) use ($search) {
            $q->where(CompletedOrder::getTableName().'.customer_email', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.payment_method', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.payment_status', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.event_id', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.object_id', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.status', 'LIKE', '%' . $search . '%');
        });
    }

    private function addStatusToQuery($query, $status) {
        return $query->where(CompletedOrder::getTableName(). '.status', '=', $status);
    }
}