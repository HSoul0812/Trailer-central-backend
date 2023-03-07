<?php

namespace App\Repositories\Inventory\Floorplan;

use Illuminate\Support\Facades\DB;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Floorplan\Payment;

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
        DB::beginTransaction();

        try {
            $floorplanPayment = Payment::create($params);
            $this->adjustBalance($floorplanPayment, $params);

             DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }

        return $floorplanPayment;
    }
    
    public function createBulk($payments) {
        // Should probably queue this
        $floorplanPayments = [];
        DB::beginTransaction();
        
        try {
            foreach($payments as $paymentData) {
                $floorplanPayment = Payment::create($paymentData);
                $this->adjustBalance($floorplanPayment, $paymentData);
                $floorplanPayments[] = $floorplanPayment;
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return collect($floorplanPayments);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }
    
    public function getByInventory($params)
    {
        if (isset($params['inventory_id'])) {
            $query = Payment::with('inventory')
                ->whereHas('inventory', function($q) use($params) {
                    $q->where('inventory.inventory_id', $params['inventory_id']);
                });
        } else {
            $query = Payment::where('id', '>', 0);  
        }
        
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

    public function getAll($params) {
        if (isset($params['dealer_id'])) {
            $query = Payment::with('inventory')
                ->whereHas('inventory', function($q) use($params) {
                    $q->whereIn('dealer_id', $params['dealer_id']);
                });
        } else {
            $query = Payment::where('id', '>', 0);  
        }
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
    
    /**
     * If type is balance, decrease floorplan balance of the inventory
     * If type is interest, increase interest amount of the inventory
     */
    private function adjustBalance(Payment $payment, array $params):void
    {
        $amount = (float) $params['amount'];
        if ($params['type'] === Payment::PAYMENT_CATEGORIES['Balance']) {
            $amount *= -1;
        }
        
        if ($params['type'] === Payment::PAYMENT_CATEGORIES['Balance']) {
            $paymentData = ['fp_balance' => (float) $payment['inventory']['fp_balance'] - (float) $params['amount']];
            
            $paymentData['fp_paid'] = $paymentData['fp_balance'] > 0 ? 0 : 1;

            Inventory::find($params['inventory_id'])->update($paymentData);
        } else {
            Inventory::find($params['inventory_id'])
                ->update(['fp_interest_paid' => (float) $payment['inventory']['fp_interest_paid'] + (float) $params['amount']]);
        }
    }

}
