<?php

namespace App\Domains\QuickBooks\Actions;

use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Models\CRM\User\Customer;
use App\Utilities\DatabaseUtil;
use Log;
use Throwable;

class CreateAddCustomerQuickBookApprovalAction
{
    /** @var CreateAddCustomerQbObjFromCustomerAction */
    private $createQbObjAction;

    public function __construct(CreateAddCustomerQbObjFromCustomerAction $createQbObjAction)
    {
        $this->createQbObjAction = $createQbObjAction;
    }

    public function execute(Customer $customer)
    {
        $qbObj = $this->createQbObjAction->execute($customer);

        try {
            QuickbookApproval::create([
                'dealer_id' => $customer->dealer_id,
                'tb_name' => Customer::getTableName(),
                'tb_primary_id' => $customer->id,
                'qb_obj' => json_encode($qbObj),
                'created_at' => now()->timezone(DatabaseUtil::DATABASE_TIMEZONE),
            ]);
        } catch (Throwable $exception) {
            Log::info($exception->getMessage());
        }
    }
}
