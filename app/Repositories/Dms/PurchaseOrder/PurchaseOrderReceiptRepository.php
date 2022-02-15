<?php

namespace App\Repositories\Dms\PurchaseOrder;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderReceipt;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepositoryInterface;

/**
 * @author Marcel
 */
class PurchaseOrderReceiptRepository implements PurchaseOrderReceiptRepositoryInterface
{
    protected const DEFAULT_PAGINATE_RECORDS = 15;

    private $sortOrders = [
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC',
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC',
        ],
    ];

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return PurchaseOrderReceipt::findOrFail($params['id']);
    }

    public function getAll($params)
    {
        $query = PurchaseOrderReceipt::query();

        $purchaseOrderCondition = [];
        $whereOrderCondition = [];
        if (!empty($params['dealer_id'])) {
            $purchaseOrderCondition[] = ['dealer_id', '=', $params['dealer_id']];
        } else {
            $whereOrderCondition[] = ['id', '>', 0];
        }

        if (!empty($params['vendor_id'])) {
            $purchaseOrderCondition[] = ['vendor_id', '=', $params['vendor_id']];
        }

        if (!empty($purchaseOrderCondition)) {
            $query = $query->whereHas('purchaseOrder', function ($query) use (
                $purchaseOrderCondition
            ) {
                $query->where($purchaseOrderCondition);
            });
        }

        if (isset($params['is_billed'])) {
            $whereOrderCondition[] = ['is_billed', '=', (int) $params['is_billed']];
        }

        if (!empty($params['search_term'])) {
            $whereOrderCondition[] = ['ref_num', 'LIKE', '%' . $params['search_term'] . '%'];
        }

        if (!empty($whereOrderCondition)) {
            $query = $query->where($whereOrderCondition);
        }

        if (!empty($params['ids'])) {
            $query = $query->whereIn('id', $params['ids']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'] ?? self::DEFAULT_PAGINATE_RECORDS)
            ->appends($params);
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
