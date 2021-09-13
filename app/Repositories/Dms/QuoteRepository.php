<?php

namespace App\Repositories\Dms;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Account\Payment;
use Illuminate\Database\Eloquent\Collection;

/**
 * @author Marcel
 */
class QuoteRepository implements QuoteRepositoryInterface {

    private $sortOrders = [
        'title' => [
            'field' => 'title',
            'direction' => 'DESC'
        ],
        '-title' => [
            'field' => 'title',
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
        'total_price' => [
            'field' => 'total_price',
            'direction' => 'DESC'
        ],
        '-total_price' => [
            'field' => 'total_price',
            'direction' => 'ASC'
        ],
        'completed_at' => [
            'field' => 'completed_at',
            'direction' => 'DESC'
        ],
        '-completed_at' => [
            'field' => 'completed_at',
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
        return UnitSale::findOrFail($params['id']);
    }

    public function getAll($params) {
        /** @var Builder $query */
        if (isset($params['dealer_id'])) {
            $query = UnitSale::where('dealer_id', '=', $params['dealer_id']);
        } else {
            $query = UnitSale::where('id', '>', 0);
        }
        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('title', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('total_price', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('inventory_vin', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhereHas('customer', function($q) use($params) {
                        $q->where('display_name', 'LIKE', '%' . $params['search_term'] . '%');
                    })
                    // also search extra inventory
                    ->orWhereHas('extraInventory', function($q) use($params) {
                        $q->where('vin', 'LIKE', '%' . $params['search_term'] . '%');
                    })
                    ->orWhereHas('inventory', function($q) use($params) {
                        $q->where('vin', 'LIKE', '%' . $params['search_term'] . '%');
                    });
            });
        }
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        if (isset($params['status'])) {
            switch ($params['status']) {
                case UnitSale::QUOTE_STATUS_ARCHIVED:
                    $query = $query->where('is_archived', '=', 1);
                    break;
                case UnitSale::QUOTE_STATUS_OPEN:
                    $query = $query
                        ->where('is_archived', '=', 0)
                        ->where('is_po', '=', 0)
                        ->doesntHave('payments');
                    break;
                case UnitSale::QUOTE_STATUS_DEAL:
                    $query = $query
                        ->where('is_archived', '=', 0)
                        ->where('is_po', '=', 0)
                        ->whereHas('payments', function($query) {
                            $query->select(DB::raw('sum(amount) as paid_amount'))
                                ->groupBy('unit_sale_id')
                                ->havingRaw('paid_amount < dms_unit_sale.total_price');
                        });
                    break;
                case UnitSale::QUOTE_STATUS_COMPLETED:
                    $query = $query
                        ->where('is_archived', '=', 0)
                        ->where(function($query) {
                            $query->where('is_po', '=', 1)
                                ->orWhereHas('payments', function($query) {
                                    $query->select(DB::raw('sum(amount) as paid_amount'))
                                        ->groupBy('unit_sale_id')
                                        ->havingRaw('paid_amount >= dms_unit_sale.total_price');
                                });
                        });
                    break;
            }
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function getTotals($params)
    {
        if (isset($params['dealer_id'])) {
            $query = UnitSale::where('dms_unit_sale.dealer_id', '=', $params['dealer_id']);
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
                    })
                    ->orWhereHas('inventory', function($q) use($params) {
                        $q->where('vin', 'LIKE', '%' . $params['search_term'] . '%');
                    });
            });
        }
        $groupedPayments = Payment::select('unit_sale_id', DB::raw('SUM(amount) as paid_amount'))
            ->leftJoin('qb_invoices', 'qb_payment.invoice_id', '=', 'qb_invoices.id')
            ->groupBy('qb_invoices.unit_sale_id');
        return $query->select(
                DB::raw('sum(dms_unit_sale.total_price) as totalFrontGross, count(*) as totalQty,
                    IF(dms_unit_sale.is_po=1 OR COALESCE(group_payment.paid_amount, 0) > 0, 1, 0) AS deal,
                    IF(dms_unit_sale.is_po=0 AND dms_unit_sale.total_price - COALESCE(group_payment.paid_amount, 0) > 0, 0, 1) AS completed_deal')
            )
            ->leftJoinSub($groupedPayments, 'group_payment', function ($join) {
                $join->on('dms_unit_sale.id', '=', 'group_payment.unit_sale_id');
            })
            ->where('dms_unit_sale.is_archived', '=', 0)
            ->groupBy(DB::raw('CASE WHEN group_payment.paid_amount OR dms_unit_sale.is_po = 1 THEN 1 ELSE 0 END, CASE WHEN dms_unit_sale.total_price - group_payment.paid_amount > 0 AND dms_unit_sale.is_po=0 THEN 0 ELSE 1 END'))
            ->get();
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

    public function getCompletedDeals(int $dealerId): Collection 
    {
        return UnitSale::where('dealer_id', '=', $dealerId)
                ->where('is_archived', '=', 0)
                ->where(function($query) {
                    $query->where('is_po', '=', 1)
                        ->orWhereHas('payments', function($query) {
                            $query->select(DB::raw('sum(amount) as paid_amount'))
                                ->groupBy('unit_sale_id')
                                ->havingRaw('paid_amount >= dms_unit_sale.total_price');
                        });
                })->orderBy('created_at', 'asc')->get();
    }

}
