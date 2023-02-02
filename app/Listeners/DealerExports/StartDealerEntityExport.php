<?php
namespace App\Listeners\DealerExports;

use App\Events\DealerExports\EntityDataExported;
use App\Models\DealerExport;

class StartDealerEntityExport
{
    public function handle(EntityDataExported $entityDataExported)
    {
        DealerExport::query()
            ->where('dealer_id', $entityDataExported->dealer->dealer_id)
            ->where('entity_type', $entityDataExported->entityType)
            ->update([
                'status' => DealerExport::STATUS_IN_PROGRESS,
            ]);
    }
}
