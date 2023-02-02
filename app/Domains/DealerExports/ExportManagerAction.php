<?php

namespace App\Domains\DealerExports;

use App\Models\User\User;
use App\Models\DealerExport;
use App\Jobs\DealerExports\DealerDataExportJob;

class ExportManagerAction
{
    protected $dealerId;

    protected $exportActions = [
        VendorsExporterAction::class,
        BrandsExporterAction::class,
    ];

    public function __construct(int $dealerId)
    {
        $this->dealerId = $dealerId;
    }

    public function execute()
    {
        $dealer = User::query()->where('dealer_id', $this->dealerId)->where('type', User::TYPE_DEALER)->firstOrFail();

        foreach($this->exportActions as $exportAction) {
            // The export job can run multiple times, so its possible that we have the data in the table.
            // We will update the entry if it already exists, else will create a new one.
            DealerExport::updateOrCreate(
                ['dealer_id' => $dealer->dealer_id, 'entity_type' => constant($exportAction . '::ENTITY_TYPE')],
                ['status' => DealerExport::STATUS_QUEUED]
            );

            DealerDataExportJob::dispatch($dealer, $exportAction);
        }
    }
}
