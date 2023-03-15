<?php

namespace App\Domains\DealerExports;

use App\Models\DealerExport;
use App\Models\User\User;

/**
 * Class ExportFinishedAction
 *
 * @package App\Domains\DealerExports
 */
class ExportFinishedAction
{
    protected $dealer;
    protected $entityType;
    protected $filePath;

    /**
     * @param User $dealer
     * @param string $entityType
     * @param string $filePath
     */
    public function __construct(User $dealer, string $entityType, string $filePath)
    {
        $this->dealer = $dealer;
        $this->entityType = $entityType;
        $this->filePath = $filePath;
    }

    /**
     * @return void
     */
    public function execute()
    {
        DealerExport::query()
            ->where('dealer_id', $this->dealer->dealer_id)
            ->where('entity_type', $this->entityType)
            ->update([
                'status' => DealerExport::STATUS_PROCESSED,
                'file_path' => $this->filePath,
            ]);

        $allExportCount = DealerExport::query()->where('dealer_id', $this->dealer->dealer_id)->count();
        $otherEntityExportCount = DealerExport::query()
            ->where('dealer_id', $this->dealer->dealer_id)
            ->where('entity_type', '!=', 'zip')
            ->where('status', DealerExport::STATUS_PROCESSED)
            ->count();

        if ($otherEntityExportCount === ($allExportCount - 1)) {
            (new CreateZipArchiveAction($this->dealer))->execute();
        }
    }
}
