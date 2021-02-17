<?php

namespace App\Services\Quickbooks;

use App\Models\CRM\Dms\Quickbooks\Account;
use App\Models\Parts\Vendor;

/**
 * Class AccountService
 *
 * @package App\Services\Quickbooks
 */
class AccountService
{
    public function getFlooringDebtAccount(int $vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $flooringDebutAccName = 'Flooring Debt - ' . $vendor->name;
        
        return Account::where([
            ['dealer_id', '=', $vendor->dealer_id],
            ['name', '=', $flooringDebutAccName],
            ['type', '=', 'Credit Card']
        ])->firstOrFail();
    }
}
