<?php

namespace App\Repositories\Bulk\Inventory;

use App\Models\Bulk\Inventory\BulkDownload;
use App\Repositories\Common\MonitoredJobRepositoryInterface;

/**
 * Describe the API for the repository of bulk download jobs
 */
interface BulkDownloadRepositoryInterface extends MonitoredJobRepositoryInterface
{
    /**
     * Find a download job by token
     *
     * @param string $token
     * @return BulkDownload|null
     */
    public function findByToken(string $token): ?BulkDownload;

    /**
     * Create a download job by token
     *
     * @param array $params Array of values for the new row
     * @return BulkDownload
     */
    public function create(array $params): BulkDownload;
}
