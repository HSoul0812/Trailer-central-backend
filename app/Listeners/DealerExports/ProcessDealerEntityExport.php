<?php
namespace App\Listeners\DealerExports;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;
use App\Models\User\User;
use App\Events\DealerExports\EntityDataExported;
use App\Models\DealerExport;

class CreateCustomerFromOrder
{
    public function handle(EntityDataExported $entityDataExported)
    {
        DealerExport::query()
            ->where('dealer_id', $entityDataExported->dealer->dealer_id)
            ->where('entity_type', $entityDataExported->entityType)
            ->update([
                'status' => DealerExport::STATUS_PROCESSED,
                'file_path' => $entityDataExported->filePath,
            ]);
    }
}
