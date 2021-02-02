<?php

namespace App\Repositories\Bulk\Parts;

use App\Models\Bulk\Parts\BulkDownload;
use App\Repositories\Common\MonitoredJobRepository;

/**
 * Implementation for bulk download repository
 */
class BulkDownloadRepository extends MonitoredJobRepository implements BulkDownloadRepositoryInterface
{
    /**
     * @param string $token
     * @return BulkDownload
     */
    public function findByToken(string $token): BulkDownload
    {
        return BulkDownload::where('token', $token)->get()->first();
    }

    /**
     * @param array $params
     *
     * @return BulkDownload
     */
    public function create(array $params): BulkDownload
    {
        return BulkDownload::create($params);
    }
}
