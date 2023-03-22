<?php

namespace App\Domains\DealerExports;

use App\Models\DealerExport;
use App\Models\User\User;

/**
 * Class ExportStartAction
 *
 * @package App\Domains\DealerExports
 */
class ExportStartAction
{
    protected $dealer;
    protected $entityType;

    /**
     * @param User $dealer
     * @param string $entityType
     */
    public function __construct(User $dealer, string $entityType)
    {
        $this->dealer = $dealer;
        $this->entityType = $entityType;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        DealerExport::query()
            ->where([
                'dealer_id' => $this->dealer->dealer_id,
                'entity_type' => $this->entityType,
            ])
            ->update([
                'status' => DealerExport::STATUS_IN_PROGRESS,
            ]);
    }
}
