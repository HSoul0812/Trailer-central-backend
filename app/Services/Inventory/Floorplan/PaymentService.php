<?php

namespace App\Services\Inventory\Floorplan;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface;
use App\Repositories\Dms\Quickbooks\ExpenseRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Models\CRM\Dms\Quickbooks\Expense;
use App\Models\CRM\Dms\Quickbooks\ItemNew;
use App\Models\Inventory\Floorplan\Payment;
use App\Services\Quickbooks\NewItemService;
use App\Services\Quickbooks\AccountService;

class PaymentService implements PaymentServiceInterface
{

    const FLOORPLAN_PAYMENT_KEY_PREFIX = 'bulk_floorplan_payment_';

    /**
     * @var Connection|null
     */
    private $redis = null;

    /**
     * @var PaymentRepositoryInterface
     */
    private $payment;

    /**
     * @var ExpenseRepositoryInterface
     */
    private $expenseRepo;

    /**
     * @var NewItemService
     */
    private $newItemService;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var InventoryRepositoryInterface
     */
    private $inventoryRepository;

    public function __construct(
        PaymentRepositoryInterface $payment,
        ExpenseRepositoryInterface $expenseRepo,
        NewItemService $newItemService,
        AccountService $accountService,
        InventoryRepositoryInterface $inventoryRepository
    )
    {
        $this->payment = $payment;
        $this->expenseRepo = $expenseRepo;
        $this->newItemService = $newItemService;
        $this->accountService = $accountService;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function validatePaymentUUID(int $dealerId, string $paymentUUID)
    {
        $this->connectToRedis();

        $bulkFloorplanPaymentKey = self::FLOORPLAN_PAYMENT_KEY_PREFIX . $dealerId;
        if ($this->redis->get($bulkFloorplanPaymentKey) === $paymentUUID) {
            return false;
        }

        return true;
    }

    public function setPaymentUUID(int $dealerId, string $paymentUUID)
    {
        $this->connectToRedis();

        $bulkFloorplanPaymentKey = self::FLOORPLAN_PAYMENT_KEY_PREFIX . $dealerId;
        $this->redis->set($bulkFloorplanPaymentKey, $paymentUUID, 'EX', 3600);
    }

    public function createBulk(int $dealerId, array $payments, string $paymentUUID)
    {
        $payments = $this->payment->createBulk($payments);
        $this->setPaymentUUID($dealerId, $paymentUUID);

        return $payments;
    }

    public function create(array $params)
    {
        DB::beginTransaction();

        try {
            // Interest Account
            $interestItem = $this->newItemService->getByItemName($params['dealer_id'], ItemNew::ITEM_INTEREST);
            if (empty($interestItem)) {
                throw new \Exception('There is no quickbook setting for Interest Paid.');
            }
            $interestAccountId = $interestItem->cogs_acc_id;
            $flooringDebtAccountId = $this->accountService->getFlooringDebtAccount($params['vendor_id'])->id;

            $categories = [];
            foreach ($params['payments'] as $payment) {
                $isBalancePayment = $payment['type'] === Payment::PAYMENT_CATEGORIES['Balance'];
                $inventory = $this->inventoryRepository->get(['id' => $payment['inventory_id']]);
                $categories[] = [
                    'account_id' => $isBalancePayment ? $flooringDebtAccountId : $interestAccountId,
                    'amount' => $payment['amount'],
                    'description' => 'VIN: ' . $inventory->vin . ', Stock: ' . $inventory->stock
                ];
            }
            // Add payment history
            $this->payment->createBulk($params['payments']);

            $expense = $this->expenseRepo->create([
                'dealer_id' => $params['dealer_id'],
                'account_id' => $params['account_id'],
                'txn_date' => date('Y-m-d'),
                'doc_num' => $params['check_number'],
                'entity_type' => Expense::ENTITY_VENDOR,
                'entity_id' => $params['vendor_id'],
                'tb_name' => Expense::TABLE_FLOORPLAN_PAYMENT,
                'total_amount' => $params['total_amount'],
                // Categories
                'categories' => $categories
            ]);
            $this->setPaymentUUID($params['dealer_id'], $params['paymentUUID']);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }

        return $expense;
    }

    /**
     * @param int $dealerId
     * @param string $checkNumber
     *
     * @return bool
     */
    public function checkNumberExists(int $dealerId, string $checkNumber): bool
    {
        $data = [
            'entity_type' => Expense::ENTITY_VENDOR,
            'tb_name' => Expense::TABLE_FLOORPLAN_PAYMENT,
        ];

        return $this->expenseRepo->checkNumberExists($dealerId, $checkNumber, $data);
    }

    private function connectToRedis(): void
    {
        if ($this->redis instanceof Connection) {
            return;
        }

        $this->redis = Redis::connection('persist');
    }
}
