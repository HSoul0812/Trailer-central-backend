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
     * @throws Exception
     */
    public function execute(Bill $bill)
    {
        $dealer = $bill->dealer;

        $this->setupQuickBooksSDKForDealerAction->execute($dealer);

        $this->deleteBillByIdAction->execute($bill->qb_id);
    }
}
