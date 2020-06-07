<?php

namespace App\Repositories\Dms;

use Illuminate\Support\Facades\DB;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Dms\QuoteStatus;

/**
 * @author Marcel
 */
class QuoteRepository implements QuoteRepositoryInterface {


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
            $query = UnitSale::whereIn('dealer_id', $params['dealer_id']);
        } else {
            $query = UnitSale::where('id', '>', 0);  
        }
        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('title', 'LIKE', '%' . $params['search_term'] . '%')
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
                case QuoteStatus::ARCHIVED:
                    $query = $query->where('is_archived', '=', 1);
                    break;
                case QuoteStatus::OPEN:
                    $query = $query
                        ->where('is_archived', '=', 0)
                        ->doesntHave('payments');
                    break;
                case QuoteStatus::DEAL:
                    $query = $query
                        ->where('is_archived', '=', 0)
                        ->whereHas('payments', function($query) {
                            $query->select(DB::raw('sum(amount) as paid_amount'))
                                ->groupBy('invoice_id')
                                ->havingRaw('paid_amount < dms_unit_sale.total_price');
                        });
                    break;
                case QuoteStatus::COMPLETED:
                    $query = $query
                        ->where('is_archived', '=', 0)
                        ->whereHas('payments', function($query) {
                            $query->select(DB::raw('sum(amount) as paid_amount'))
                                ->groupBy('invoice_id')
                                ->havingRaw('paid_amount >= dms_unit_sale.total_price');
                        });
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
