<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\CRM\Dms\Quickbooks\BillCategory;
use App\Models\CRM\Dms\Quickbooks\BillItem;
use App\Models\CRM\Dms\Quickbooks\BillPayment;
use App\Models\Inventory\Inventory;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface;
use App\Repositories\Traits\SortTrait;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Class BillRepository
 * @package App\Repositories\Dms\Quickbooks
 */
class BillRepository implements BillRepositoryInterface
{

    use SortTrait, Transaction;

    private const DEFAULT_PAGE_SIZE = 15;

    private $sortOrders = [
        'status' => [
            'field' => 'status',
            'direction' => 'DESC'
        ],
        '-status' => [
            'field' => 'status',
            'direction' => 'ASC'
        ],
        'received_date' => [
            'field' => 'received_date',
            'direction' => 'DESC'
        ],
        '-received_date' => [
            'field' => 'received_date',
            'direction' => 'ASC'
        ],
        'due_date' => [
            'field' => 'due_date',
            'direction' => 'DESC'
        ],
        '-due_date' => [
            'field' => 'due_date',
            'direction' => 'ASC'
        ],
    ];

    /**
     * @param $params
     * @return Bill
     */
    public function create($params): Bill
    {
        $items = [];
        $categories = [];
        $payments = [];

        if (isset($params['items'])) {
            $items = $params['items'];
            unset($params['items']);
        }

        if (isset($params['categories'])) {
            $categories = $params['categories'];
            unset($params['categories']);
        }

        if (isset($params['payments'])) {
            $payments = $params['payments'];
            unset($params['payments']);
        }

        $this->beginTransaction();

        try {
            $bill = new Bill($params);
            $bill->save();

            foreach ($items as $item) {
                $item['bill_id'] = $bill->id;
                $billItem = new BillItem($item);
                $billItem->save();
            }

            foreach ($categories as $category) {
                $category['bill_id'] = $bill->id;
                $billCategory = new BillCategory($category);
                $billCategory->save();
            }

            foreach ($payments as $payment) {
                $payment['bill_id'] = $bill->id;
                $billPayment = new BillPayment($payment);
                $billPayment->save();
            }

            $this->commitTransaction();
        }
        catch (\Exception $exception)
        {
            $this->rollbackTransaction();
        }

        return $bill;
    }

    /**
     * @param array $params
     * @return Bill
     *
     * @throws ModelNotFoundException
     */
    public function update($params): Bill
    {
        $items = [];
        $categories = [];
        $payments = [];

        if (isset($params['items'])) {
            $items = $params['items'];
            unset($params['items']);
        }

        if (isset($params['categories'])) {
            $categories = $params['categories'];
            unset($params['categories']);
        }

        if (isset($params['payments'])) {
            $payments = $params['payments'];
            unset($params['payments']);
        }

        $bill = Bill::findOrFail($params['id']);
        $bill->fill($params)->save();

        $this->beginTransaction();
        try {
            if (!empty($items)) {
                DB::statement("DELETE FROM qb_bill_items WHERE bill_id = " . $bill->id);
                foreach ($items as $item) {
                    $item['bill_id'] = $bill->id;
                    $billItem = new BillItem($item);
                    $billItem->save();
                }
            }

            if (!empty($categories)) {
                DB::statement("DELETE FROM qb_bill_categories WHERE bill_id = " . $bill->id);
                foreach ($categories as $category) {
                    $category['bill_id'] = $bill->id;
                    $billCategory = new BillCategory($category);
                    $billCategory->save();
                }
            }


            if (!empty($payments)) {
                DB::statement("DELETE FROM qb_bill_payment WHERE bill_id = " . $bill->id);
                foreach ($payments as $payment) {
                    $payment['bill_id'] = $bill->id;
                    $billPayment = new BillPayment($payment);
                    $billPayment->save();
                }
            }

            $this->commitTransaction();
        } catch (\Exception $exception) {
            $this->rollbackTransaction();
        }

        return $bill;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function get($params)
    {
        $with = Arr::get($params, 'with', []);
        
        return Bill::query()
            ->with($with)
            ->findOrFail($params['id']);
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        /** @var Bill $bill */
        $bill = $this->get([
            'id' => $params['id'],
            'with' => [
                'inventories',
                'items',
                'categories',
                'payments'
            ],
        ]);
        
        // Note: The logic below is taken from https://operatebeyond.atlassian.net/browse/DMSS-645?focusedCommentId=30120
        // First, we want to make sure to remove bill related data on the
        // inventories that use this bill
        $bill->inventories()->update([
            'fp_committed' => null,
            'fp_vendor' => null,
            'fp_balance' => 0,
            'fp_paid' => 0,
            'fp_interest_paid' => 0,
            'bill_id' => null,
            'send_to_quickbooks' => 0,
            'is_floorplan_bill' => 0,
            'qb_sync_processed' => 0
        ]);

        // Next, we delete the bill items
        $bill->items()->delete();

        // After that, we remove the bill categories
        $bill->categories()->delete();

        // Then, we remove any payments from the bill
        $bill->payments()->delete();
        
        // Lastly, we delete the bill itself
        $bill->delete();
    }

    /**
     * @param array $params
     * @param bool $paginated
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $paginated = false)
    {
        if ($paginated) {
            return $this->getPaginatedResults($params);
        }

        $query = $this->buildInventoryQuery($params);

        return $query->get();
    }

    private function getPaginatedResults($params)
    {
        $perPage = !isset($params['per_page']) ? self::DEFAULT_PAGE_SIZE : (int)$params['per_page'];
        $currentPage = !isset($params['page']) ? 1 : (int)$params['page'];

        $paginatedQuery = $this->buildInventoryQuery($params);
        $resultsCount = $this->getResultsCountFromQuery($paginatedQuery);

        $paginatedQuery->skip(($currentPage - 1) * $perPage);
        $paginatedQuery->take($perPage);

        return (new LengthAwarePaginator(
            $paginatedQuery->get(),
            $resultsCount,
            $perPage,
            $currentPage,
            ["path" => URL::to('/')."/api/bills"]
        ))->appends($params);
    }

    /**
     * @param array $params
     * @param bool $withDefault whether to apply default conditions or not
     *
     * @return Builder
     */
    private function buildInventoryQuery(array $params): Builder
    {
        /** @var Builder $query */
        $query = Bill::query()->select(['qb_bills.*'])->where('qb_bills.id', '>', 0);

        if (isset($params['status'])) {
            $query = $query->where('status', $params['status']);
        }

        if (isset($params['received_date'])) {
            $query = $query->where('received_date', $params['received_date']);
        }

        if (isset($params['due_date'])) {
            $query = $query->where('due_date', $params['due_date']);
        }

        if (isset($params['dealer_id'])) {
            $query = $query->where('qb_bills.dealer_id', $params['dealer_id']);
        }

        if (isset($params['dealer_location_id'])) {
            $query = $query->where('qb_bills.dealer_location_id', $params['dealer_location_id']);
        }

        if (isset($params['search_term'])) {
            $query = $query->where('qb_bills.doc_num', 'LIKE', '%'.$params['search_term'].'%');
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query;
    }

    private function getResultsCountFromQuery(Builder $query) : int
    {
        $queryString = str_replace(array('?'), array('\'%s\''), $query->toSql());
        $queryString = vsprintf($queryString, $query->getBindings());
        return current(DB::select(DB::raw("SELECT count(*) as row_count FROM ($queryString) as bill_count")))->row_count;
    }
}
