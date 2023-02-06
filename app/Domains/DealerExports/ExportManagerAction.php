<?php

namespace App\Domains\DealerExports;

use App\Models\User\User;
use App\Models\DealerExport;
use App\Jobs\DealerExports\DealerDataExportJob;
use App\Domains\DealerExports\BackOffice\Settings\VendorsExportAction;
use App\Domains\DealerExports\BackOffice\Settings\BrandsExportAction;
use App\Domains\DealerExports\BackOffice\Settings\EmployeesExportAction;
use App\Domains\DealerExports\BackOffice\Settings\ExpensesExportAction;
use App\Domains\DealerExports\BackOffice\FinancingCompaniesExportAction;

class ExportManagerAction
{
    protected $dealer;

    protected $exportActions = [
        // BackOffice -> Settings
        VendorsExportAction::class,
        BrandsExportAction::class,
        EmployeesExportAction::class,
        ExpensesExportAction::class,
        // BackOffice -> Financial Companies
        FinancingCompaniesExportAction::class,
    ];

    public function __construct(User $dealer)
    {
        $this->dealer = $dealer;
    }

    public function execute()
    {
        foreach($this->exportActions as $exportAction) {
            // The export job can run multiple times, so its possible that we have the data in the table.
            // We will update the entry if it already exists, else will create a new one.
            DealerExport::updateOrCreate(
                ['dealer_id' => $this->dealer->dealer_id, 'entity_type' => constant($exportAction . '::ENTITY_TYPE')],
                ['status' => DealerExport::STATUS_QUEUED]
            );

            DealerDataExportJob::dispatch($this->dealer, $exportAction);
        }
    }
}
