<?php

namespace App\Domains\DealerExports;

use App\Domains\DealerExports\BackOffice\BillsExportAction;
use App\Domains\DealerExports\BackOffice\CustomersExportAction;
use App\Domains\DealerExports\BackOffice\FinancingCompaniesExportAction;
use App\Domains\DealerExports\BackOffice\Settings\BrandsExportAction;
use App\Domains\DealerExports\BackOffice\Settings\EmployeesExportAction;
use App\Domains\DealerExports\BackOffice\Settings\ExpensesExportAction;
use App\Domains\DealerExports\BackOffice\Settings\PaymentMethodsExportAction;
use App\Domains\DealerExports\BackOffice\Settings\VendorsExportAction;
use App\Jobs\DealerExports\DealerDataExportJob;
use App\Models\DealerExport;
use App\Models\User\User;
use App\Domains\DealerExports\POS\SalesLedgerExport;
use App\Domains\DealerExports\POS\RefundsExportAction;
use App\Domains\DealerExports\Service\RepairOrdersExport;
use App\Domains\DealerExports\Quotes\QuotesExportAction;
use App\Domains\DealerExports\BackOffice\PurchaseOrdersExportAction;

/**
 * Class ExportManagerAction
 *
 * @package App\Domains\DealerExports
 */
class ExportManagerAction
{
    protected $dealer;

    protected $exportActions = [
        // BackOffice -> Settings
        VendorsExportAction::class,
        BrandsExportAction::class,
        EmployeesExportAction::class,
        ExpensesExportAction::class,
        PaymentMethodsExportAction::class,
        // BackOffice -> Financial Companies
        FinancingCompaniesExportAction::class,
        // BackOffice -> Customers
        CustomersExportAction::class,
        // BackOffice -> Bills
        BillsExportAction::class,
        // BackOffice -> Purchase Order
        PurchaseOrdersExportAction::class,
        // POS -> Sales
        SalesLedgerExport::class,
        // POS -> Sales -> Refunds
        RefundsExportAction::class,
        // Service
        RepairOrdersExport::class,
        // Quotes
        QuotesExportAction::class
    ];

    /**
     * @param User $dealer
     */
    public function __construct(User $dealer)
    {
        $this->dealer = $dealer;
    }

    /**
     * @return void
     */
    public function execute()
    {
        foreach ($this->exportActions as $exportAction) {
            // The export job can run multiple times, so its possible that we have the data in the table.
            // We will update the entry if it already exists, else will create a new one.
            DealerExport::updateOrCreate(
                ['dealer_id' => $this->dealer->dealer_id, 'entity_type' => constant($exportAction . '::ENTITY_TYPE')],
                ['status' => DealerExport::STATUS_QUEUED, 'file_path' => '']
            );

            DealerDataExportJob::dispatch($this->dealer, $exportAction);
        }
    }
}
