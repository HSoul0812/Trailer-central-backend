<?php

namespace App\Domains\QuickBooks\Actions;

use App\Models\CRM\Dms\Quickbooks\Bill;
use Exception;
use TCentral\QboSdk\Domain\Bill\Actions\DeleteBillByIdAction;

class DeleteBillInQuickBooksAction
{
    /** @var SetupQuickBooksSDKForDealerAction */
    private $setupQuickBooksSDKForDealerAction;

    /** @var DeleteBillByIdAction */
    private $deleteBillByIdAction;

    public function __construct(
        SetupQuickBooksSDKForDealerAction $setupQuickBooksSDKForDealerAction,
        DeleteBillByIdAction $deleteBillByIdAction
    )
    {
        $this->setupQuickBooksSDKForDealerAction = $setupQuickBooksSDKForDealerAction;
        $this->deleteBillByIdAction = $deleteBillByIdAction;
    }

    /**
     * If this method returns nothing then it means the delete operation is a success
     * If it throws an exception then something is wrong
     *
     * @throws Exception
     */
    public function execute(Bill $bill): void
    {
        // Set up the SDK
        $this->setupQuickBooksSDKForDealerAction->execute($bill->dealer);

        // Delete the bill from QBO
        $this->deleteBillByIdAction->execute($bill->qb_id);

        // Set the qb_id back to null
        $bill->qb_id = null;
        $bill->save();
    }
}
