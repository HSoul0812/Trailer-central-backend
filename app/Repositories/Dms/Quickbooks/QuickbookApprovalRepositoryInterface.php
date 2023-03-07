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

    /**
     * @param int $tbPrimaryId
     * @param string $tableName
     * @param int $dealerId
     * @return bool
     */
    public function deleteByTbPrimaryId(int $tbPrimaryId, string $tableName, int $dealerId);

    /**
     * Create an approval object for a customer
     *
     * @param  Customer  $customer
     * @return QuickbookApproval|mixed|void
     * @throws \Exception
     */
    public function createForCustomer(Customer $customer);
}
