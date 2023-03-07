<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Domains\QuickBooks\Actions\DeleteBillInQuickBooksAction;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\CRM\Dms\Quickbooks\BillCategory;
use App\Models\CRM\Dms\Quickbooks\BillItem;
use App\Models\CRM\Dms\Quickbooks\BillPayment;
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

    private const ACTION_ADD = 'add';
    private const ACTION_UPDATE = 'update';

    /**
     * @var BillPaymentRepositoryInterface
     */
    private $billPaymentRepository;

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
     * @param BillPaymentRepositoryInterface $billPaymentRepository
     */
    public function __construct(BillPaymentRepositoryInterface $billPaymentRepository)
    {
        $this->billPaymentRepository = $billPaymentRepository;
    }

    /**
     * @param $params
     *
     * @return Bill
     */
    public function create($params): Bill
    {
        try {
            $this->beginTransaction();

            $bill = new Bill(Arr::except($params, ['items', 'categories', 'payments']));
            $bill->save();

            $this->save($params, $bill);

            $this->commitTransaction();
            return $bill;
        } catch (\Exception $exception) {
            $this->rollbackTransaction();
        }
    }

    /**
     * @param array $params
     *
     * @return Bill
     *
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function update($params): Bill
    {
        try {
            $this->beginTransaction();

            $bill = Bill::findOrFail($params['id']);

            $bill->fill(
                Arr::except($params, ['items', 'categories', 'payments'])
            )->save();

            $this->save($params, $bill, self::ACTION_UPDATE);

            $this->commitTransaction();

            return $bill;
        } catch (\Exception $exception) {
            $this->rollbackTransaction();

            throw $exception;
        }
    }

    /**
     * @param array $categories
     * @param Bill $bill
     * @param string $action
     *
     * @return void
     */
    public function saveCategories(array $categories, Bill $bill, string $action = self::ACTION_ADD): void
    {
        $data = [
            'bill_id' => $bill->getKey(),
        ];

        if (!empty($categories)) {
            if ($action === self::ACTION_UPDATE) {
                $bill->categories()->delete();
            }

            foreach ($categories as $category) {
                $billCategory = new BillCategory($data + $category);
                $billCategory->save();
            }
        }
    }

    /**
     * @param array $items
     * @param Bill $bill
     * @param string $action
     *
     * @return void
     */
    public function saveItems(array $items, Bill $bill, string $action = self::ACTION_ADD): void
    {
        $data = [
            'bill_id' => $bill->getKey(),
        ];

        if (!empty($items)) {
            if ($action === self::ACTION_UPDATE) {
                $bill->items()->delete();
            }

            foreach ($items as $item) {
                $billItem = new BillItem($data + $item);
                $billItem->save();
            }
        }
    }

    /**
     * @param array $payments
     * @param Bill $bill
     * @param string $action
     *
     * @return void
     */
    public function saveBillPayment(array $payments, Bill $bill, string $action = self::ACTION_ADD): void
    {
        $data = [
            'bill_id' => $bill->getKey(),
            'dealer_id' => $bill->dealer_id,
        ];

        if (!empty($payments)) {
            if ($action === self::ACTION_UPDATE) {
                $bill->payments()->delete();
            }

            foreach ($payments as $payment) {
                $this->billPaymentRepository->create($data + $payment);
            }
        }
    }

    /**
     * @param array $params
     * @param Bill $bill
     *
     * @return Bill
     */
    private function save(
        array $params,
        Bill $bill,
        string $action = self::ACTION_ADD
    ): Bill {
        // Bill Line Items
        $this->saveItems($params['items'] ?? [], $bill, $action);

        // Bill Categories
        $this->saveCategories($params['categories'] ?? [], $bill, $action);

        // Bill Payments
        $this->saveBillPayment($params['payments'] ?? [], $bill, $action);

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
     * @throws \Exception
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

        // First of all, delete the bill on QBO
        resolve(DeleteBillInQuickBooksAction::class)
            ->execute($bill);

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

        // Then, delete related records from the quickbook_approval table
        // We need to do this before deleting all those data
        $this->deleteRelatedQuickbookApprovals($bill);

        // After that, we start delete the related models
        $this->deleteRelatedModels($bill);
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
            ["path" => URL::to('/') . "/api/bills"]
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
            $query = $query->where('qb_bills.doc_num', 'LIKE', '%' . $params['search_term'] . '%');
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query;
    }

    private function getResultsCountFromQuery(Builder $query): int
    {
        $queryString = str_replace(array('?'), array('\'%s\''), $query->toSql());
        $queryString = vsprintf($queryString, $query->getBindings());
        return current(DB::select(DB::raw("SELECT count(*) as row_count FROM ($queryString) as bill_count")))->row_count;
    }

    private function deleteRelatedQuickbookApprovals(Bill $bill)
    {
        /** @var BillPayment $payment */
        foreach ($bill->payments as $payment) {
            $payment->approvals()->delete();
        }

        $bill->approvals()->delete();
    }

    /**
     * @throws \Exception
     */
    private function deleteRelatedModels(Bill $bill)
    {
        // Here, we delete all the related models from the database
        $bill->items()->delete();
        $bill->categories()->delete();
        $bill->payments()->delete();

        // After that, we delete the bill itself
        $bill->delete();
    }
}
