<?php

namespace App\Repositories\Bulk\Parts;

use App\Models\Bulk\Parts\BulkReport;
use App\Repositories\Common\MonitoredJobRepository;

/**
 * Implementation for bulk report repository
 */
class BulkReportRepository extends MonitoredJobRepository implements BulkReportRepositoryInterface
{
    /**
     * @param string $token
     * @return BulkReport|null
     */
    public function findByToken(string $token): ?BulkReport
    {
        return BulkReport::where('token', $token)->get()->first();
    }

    /**
     * @param array $params
     *
     * @return BulkReport
     */
    public function create(array $params): BulkReport
    {
        return BulkReport::create($params);
    }
}
