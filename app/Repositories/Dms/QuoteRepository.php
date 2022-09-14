<?php

namespace App\Repositories\Dms;

use App\Exceptions\RepositoryInvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\Account\Payment;
use Illuminate\Database\Eloquent\Collection;

/**
 * @author Marcel
 */
class QuoteRepository implements QuoteRepositoryInterface
{
    /**
     * @param UnitSale $unitSale
     */
    public function __construct(UnitSale $unitSale)
    {
        $this->model = $unitSale;
    }

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
            'field' => 'invoice.invoice_date',
            'direction' => 'DESC'
        ],
        '-completed_at' => [
            'field' => 'invoice.invoice_date',
            'direction' => 'ASC'
        ],
    ];

    private function calculatedPayments()
    {
        return Payment::select(DB::raw('qb_payment.id as id, qb_payment.amount - coalesce(sum(dealer_refunds.amount), 0) as balance'))
            ->leftJoin('dealer_refunds', function ($join) {
                $join->on('qb_payment.id', '=', 'dealer_refunds.tb_primary_id')
                    ->where('dealer_refunds.tb_name', '=', 'qb_payment');
            })->groupBy('qb_payment.id');
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
        return UnitSale::findOrFail($params['id']);
    }

    public function getAll($params)
    {
        /** @var Builder $query */
        if (isset($params['dealer_id'])) {
            $query = UnitSale::where('dealer_id', '=', $params['dealer_id']);
        } else {
            $query = UnitSale::where('id', '>', 0);
        }

        // Filter out service orders which location doesn't exist
        if (isset($params['location']) && $params['location'] > 0) {
            $query = $query->where('dealer_location_id', '=', $params['location']);
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
                    $query = $query
                        ->where('is_sold', '=', 0)
                        ->where('is_archived', '=', 1);
                    break;
                case UnitSale::QUOTE_STATUS_OPEN:
                    $query = $query
                        ->where('is_sold', '=', 0)
                        ->where('is_archived', '=', 0)
                        ->where('is_po', '=', 0)
                        ->doesntHave('payments');
                    break;
                case UnitSale::QUOTE_STATUS_DEAL:
                    $query = $query
                        ->where('is_sold', '=', 0)
                        ->where('is_archived', '=', 0)
                        ->where('is_po', '=', 0)
                        ->whereHas('payments', function(Builder $query) {
                            $query->select(DB::raw('sum(amount) as paid_amount'))
                            ->groupBy('unit_sale_id')
                            ->havingRaw('paid_amount < dms_unit_sale.total_price');
                        });
                    break;
                case UnitSale::QUOTE_STATUS_COMPLETED:
                    $query = $query->where(function (Builder $query) {
                        $query
                            ->where('is_sold', '=', 1)
                            ->orWhere(function (Builder $query) {
                                $query
                                    ->where('is_archived', '=', 0)
                                    ->where(function (Builder $query) {
                                        $query->where('is_po', '=', 1)
                                            ->orWhereHas('payments', function (Builder $query) {
                                                $query->select(DB::raw('sum(calculated_payments.balance) as calculated_amount'))
                                                    ->leftJoinSub($this->calculatedPayments(), 'calculated_payments', function (JoinClause $join) {
                                                        $join->on('qb_payment.id', '=', 'calculated_payments.id');
                                                    })
                                                    ->groupBy('unit_sale_id')
                                                    ->havingRaw('calculated_amount >= dms_unit_sale.total_price');
                                            });
                                    });
                            });
                    });
                    break;
            }
        }

        if (isset($params['lead_id'])) {
            $query->where('lead_id', '=', $params['lead_id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        } else {
            $query = $this->addSortQuery($query, 'created_at');
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
            $query = $query->where(function ($q) use ($params) {
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
        $groupedPayments = Payment::select(DB::raw('sum(calculated_payments.balance) as calculated_amount, sum(amount) as paid_amount, qb_invoices.unit_sale_id as unit_sale_id'))
            ->rightJoin('qb_invoices', 'qb_payment.invoice_id', '=', 'qb_invoices.id')
            ->joinSub($this->calculatedPayments(), 'calculated_payments', function ($join) {
                $join->on('qb_payment.id', '=', 'calculated_payments.id');
            })
            ->groupBy('qb_invoices.unit_sale_id');
        return $query->select(
                    DB::raw('sum(dms_unit_sale.total_price) as totalFrontGross, count(*) as totalQty,
                        IF(dms_unit_sale.is_po=1 OR COALESCE(group_payment.paid_amount, 0) > 0, 1, 0) AS deal,
                        IF(dms_unit_sale.is_po=1 OR dms_unit_sale.total_price - CAST(group_payment.calculated_amount as DECIMAL(10, 2)) > 0, 0, 1) AS completed_deal')
                )
                ->leftJoinSub($groupedPayments, 'group_payment', function ($join) {
                    $join->on('dms_unit_sale.id', '=', 'group_payment.unit_sale_id');
                })
                ->where('dms_unit_sale.is_archived', '=', 0)
                ->groupBy(DB::raw('CASE WHEN group_payment.paid_amount OR dms_unit_sale.is_po = 1 THEN 1 ELSE 0 END,
                    CASE WHEN dms_unit_sale.total_price - CAST(group_payment.calculated_amount as DECIMAL(10, 2)) > 0 OR dms_unit_sale.is_po=1 THEN 0 ELSE 1 END'))
                ->get();
    }

    public function update($params)
    {
        throw new NotImplementedException;
    }

    private function addSortQuery($query, $sort)
    {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }
        $sortOrder = $this->sortOrders[$sort];
        $query->select('dms_unit_sale.*');

        if($sortOrder['field'] == 'invoice.invoice_date') {
            $invoice = Invoice::select('id', 'unit_sale_id', 'invoice_date')->groupBy('unit_sale_id');
            $query = $query->leftJoinSub($invoice, 'invoice', function($join) {
                $join->on('dms_unit_sale.id', '=', 'invoice.unit_sale_id');
            });
        }

        return $query->orderBy($sortOrder['field'], $sortOrder['direction']);
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

    /**
     * @param int $dealerId
     * @param array $quoteIds
     * @return bool
     */
    public function bulkArchive(int $dealerId, array $quoteIds): bool
    {
        return (bool) $this->model::query()
            ->where('dealer_id', $dealerId)
            ->whereIn('id', $quoteIds)
            ->update([
                'is_archived' => true,
            ]);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function bulkUpdate(array $params): bool
    {
        if ((empty($params['ids']) || !is_array($params['ids'])) && (empty($params['search']) || !is_array($params['search']))) {
            throw new RepositoryInvalidArgumentException('ids or search param has been missed. Params - ' . json_encode($params));
        }

        $query = $this->model::query();

        if (!empty($params['ids']) && is_array($params['ids'])) {
            $query->whereIn('id', $params['ids']);
            unset($params['ids']);
        }

        if (!empty($params['search']['lead_id'])) {
            $query->where('lead_id', $params['search']['lead_id']);
            unset($params['search']);
        }

        return (bool)$query->update($params);
    }

    public function getRefunds(array $params): array
    {
        return [];
    }
}
