<?php

namespace App\Repositories\Ecommerce;

use App\Events\Ecommerce\OrderSuccessfullyPaid;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * @param int $dealerId
     * @return array{total_all: float,total_qty: int }
     */
    public function getGrandTotals(int $dealerId): array
    {
        return [
            'total_all' => $this->getTotalAmount($dealerId),
            'total_qty' => $this->getTotalQty($dealerId),
        ];
    }

    private function getTotalAmount(int $dealerId): float
    {
        return CompletedOrder::query()->where('dealer_id', $dealerId)->sum('total_amount');
    }

    private function getTotalQty(int $dealerId): float
    {
        $completedOrders = CompletedOrder::select('parts')->where('dealer_id', $dealerId)->get();

        $totalQty = 0;
        foreach ($completedOrders as $completedOrder)
        {
            if (!empty($completedOrder['parts'])) {
                $parts = $completedOrder['parts'];
                if ($parts) {
                    array_map(function ($part) use (&$totalQty) { $totalQty += $part['qty']; }, $parts);
                }
            }
        }

        return $totalQty;
    }

    /**
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAll($params): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        if (empty($params['dealer_id'])) {
            throw new \InvalidArgumentException('RefundRepository::getAll requires at least one argument of: "dealer_id" or "order_id" to filter by');
        }

        if (!isset($params['per_page'])) {
          $params['per_page'] = 100;
        }

        $query = CompletedOrder::select('*')->where('dealer_id', '=', $params['dealer_id']);

        /**
         * Filters
         */
        $query = $this->addFiltersToQuery($query, $params);

        if (isset($params['sort'])) {
            $query = $query->orderBy($this->sortOrders[$params['sort']]['field'], $this->sortOrders[$params['sort']]['direction']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * Create or update an order
     *
     * @param array $params
     * @return CompletedOrder
     *
     * @throws \InvalidArgumentException when "dealer_id" was not provided (only for creation)
     */
    public function create($params): CompletedOrder
    {
        /** @var CompletedOrder $completedOrder */
        $completedOrder = CompletedOrder::where('object_id', $params['object_id'])->first();

        if (!$completedOrder) {
            if (empty($params['dealer_id'])) {
                throw new \InvalidArgumentException('"dealer_id" is required');
            }

            return CompletedOrder::create($params);
        }

        $wasNotPaid = !$completedOrder->ispaid();

        $attributesToUpdate = collect($params)->only([
            'customer_email',
            'total_amount',
            'payment_method',
            'stripe_customer',
            'payment_status',
            'payment_intent',
        ])->toArray();

        $completedOrder->fill($attributesToUpdate)->save();

        if ($wasNotPaid && $completedOrder->isPaid()) {
            event(new OrderSuccessfullyPaid($completedOrder));
        }

        return $completedOrder;
    }

    /**
     * @param  array  $params
     * @return bool
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update($params): bool
    {
        $order = $this->get($params);

        return $order && $order->fill($params)->save();
    }

    /**
     * @param array $params
     * @return CompletedOrder
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \InvalidArgumentException when "id" or "ecommerce_order_id" was not provided
     */
    public function get($params): CompletedOrder
    {
        if (isset($params['id'])) {
            return CompletedOrder::findOrFail($params['id']);
        }

        if (isset($params['ecommerce_order_id'])) {
            return CompletedOrder::query()->where('ecommerce_order_id', $params['ecommerce_order_id'])->firstOrFail();
        }

        throw new \InvalidArgumentException('RefundRepository::get requires at least one argument of: "id" or "ecommerce_order_id" to filter by');
    }

    public function delete($params)
    {
        // TODO: Implement delete() method.
    }

    /**
     * it will return a PO number like this pattern: PO-{dealer_id}{next_number} e.g: PO-10011, PO-10012
     *
     * This always should be wrapped in a transaction, because we need to lock the rows for the provided dealer.
     *
     * @param int $dealerId
     * @return string
     */
    public function generateNextPoNumber(int $dealerId): string
    {
        // we need to look the rows for the provided dealer, then generate the next number
        // it will be released when transaction ended
        CompletedOrder::query()->where('dealer_id', $dealerId)->lockForUpdate()->get(['po_number']);

        /** @var CompletedOrder $order */
        $order = CompletedOrder::query()->selectRaw("CAST(REPLACE(po_number, 'PO-','') AS UNSIGNED) AS po_number")
            ->where('dealer_id', $dealerId)
            ->whereNotNull('po_number')
            ->orderByRaw('1 DESC')
            ->first(['po_number']);

        if (is_null($order)) {
            return sprintf('PO-%d%d', $dealerId, 1);
        }

        return sprintf('PO-%d', (int)$order->po_number + 1);
    }

    private function addFiltersToQuery(Builder $query, array $filters, bool $noStatusJoin = false): Builder
    {
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

    /**
     * @param Builder $query
     * @param string $dateTo
     * @return Builder
     */
    private function addDateToToQuery(Builder $query, string $dateTo): Builder {
        return $query->where(CompletedOrder::getTableName().'.created_at', '<=', $dateTo);
    }

    /**
     * @param Builder $query
     * @param string $dateFrom
     * @return Builder
     */
    private function addDateFromToQuery(Builder $query, string $dateFrom): Builder {
        return $query->where(CompletedOrder::getTableName().'.created_at', '>=', $dateFrom);
    }

    /**
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    private function addSearchToQuery(Builder $query, string $search): Builder {;
        return $query->where(function($q) use ($search) {
            $q->where(CompletedOrder::getTableName().'.customer_email', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.payment_method', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.payment_status', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.event_id', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.object_id', 'LIKE', '%' . $search . '%')
                ->orWhere(CompletedOrder::getTableName().'.status', 'LIKE', '%' . $search . '%');
        });
    }

    /**
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    private function addStatusToQuery(Builder  $query, string $status): Builder {
        return $query->where(CompletedOrder::getTableName(). '.status', '=', $status);
    }

    /**
     * @param int $orderId
     * @param array|string $error
     * @param string $stage
     * @return bool
     */
    public function logError(int $orderId, $error, string $stage): bool
    {
        return $this->get(['id' => $orderId])->addError($error, $stage);
    }
}
