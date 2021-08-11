<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Models\CRM\User\Customer;
use \App\Repositories\Repository;
use Illuminate\Support\Collection;

/**
 * @author Marcel
 */
interface QuickbookApprovalRepositoryInterface extends Repository {

    /**
     * Returns all quickbook approvals for invoices and not approved
     * @return Collection
     */
    public function getPoInvoiceApprovals($dealerId);

    public function deleteByTbPrimaryId(int $tbPrimaryId, string $tableName);

    /**
     * Create an approval object for a customer
     *
     * @param  Customer  $customer
     * @return QuickbookApproval|mixed|void
     * @throws \Exception
     */
    public function createForCustomer(Customer $customer);

}
