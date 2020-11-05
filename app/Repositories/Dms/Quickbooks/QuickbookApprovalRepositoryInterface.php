<?php

namespace App\Repositories\Dms\Quickbooks;

use \App\Repositories\Repository;

/**
 * @author Marcel
 */
interface QuickbookApprovalRepositoryInterface extends Repository {

    /**
     * Returns all quickbook approvals for invoices and not approved
     * @return Collection
     */
    public function getPoInvoiceApprovals($dealerId);

    public function deleteByTbPrimaryId(int $tbPrimaryId);
}
