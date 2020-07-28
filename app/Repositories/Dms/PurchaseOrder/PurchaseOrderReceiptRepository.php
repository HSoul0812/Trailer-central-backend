<?php

namespace App\Repositories\Dms\PurchaseOrder;

use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\PurchaseOrderReceipt;

/**
 * @author Marcel
 */
class PurchaseOrderReceiptRepository implements PurchaseOrderReceiptRepositoryInterface {

    private $sortOrders = [
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
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
            $query = PurchaseOrderReceipt::whereHas('purchaseOrder', function($query) use($params) {
                $query->where('dealer_id', '=', $params['dealer_id']);
            });
        } else {
            $query = PurchaseOrderReceipt::where('id', '>', 0);  
        }
        if (isset($params['vendor_id'])) {
            $query = $query->whereHas('purchaseOrder', function($query) use($params) {
                $query->where('vendor_id', '=', $params['vendor_id']);
            });
        }
        if (isset($params['search_term'])) {
            $query = $query->where('ref_num', 'LIKE', '%' . $params['search_term'] . '%');
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
