<?php

namespace App\Domains\DealerExports;

use App\Models\User\User;
use App\Models\DealerExport;

class ExportStartAction
{
    protected $dealer;
    protected $entityType;

    public function __construct(User $dealer, string $entityType)
    {
        $this->dealer = $dealer;
        $this->entityType = $entityType;
    }

    public function execute()
    {
        DealerExport::query()
            ->where('dealer_id', $this->dealer->dealer_id)
            ->where('entity_type', $this->entityType)
            ->update([
                'status' => DealerExport::STATUS_IN_PROGRESS,
            ]);
    }
}
