<?php

declare(strict_types=1);

namespace App\Repositories\Bulk\Parts;

use App\Models\Bulk\Parts\BulkReport;
use App\Repositories\Common\MonitoredJobRepositoryInterface;

interface BulkReportRepositoryInterface extends MonitoredJobRepositoryInterface
{
    /**
     * Find a download job by token
     *
     * @param string $token
     * @return BulkReport|null
     */
    public function findByToken(string $token): ?BulkReport;

    /**
     * Create a download job by token
     *
     * @param array $params Array of values for the new row
     * @return BulkReport
     */
    public function create(array $params): BulkReport;
}
