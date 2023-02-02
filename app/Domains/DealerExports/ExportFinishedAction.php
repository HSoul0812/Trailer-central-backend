<?php

namespace App\Domains\DealerExports;

use App\Models\User\User;
use App\Models\DealerExport;

class ExportFinishedAction
{
    protected $dealer;
    protected $entityType;
    protected $filePath;

    public function __construct(User $dealer, string $entityType, string $filePath)
    {
        $this->dealer = $dealer;
        $this->entityType = $entityType;
        $this->filePath = $filePath;
    }

    public function execute()
    {
        DealerExport::query()
            ->where('dealer_id', $this->dealer->dealer_id)
            ->where('entity_type', $this->entityType)
            ->update([
                'status' => DealerExport::STATUS_PROCESSED,
                'file_path' => $this->filePath,
            ]);
    }
}
