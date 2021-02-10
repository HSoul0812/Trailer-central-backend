<?php

declare(strict_types=1);

namespace App\Models\Bulk\Parts;

use App\Models\Common\MonitoredJob;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;

/**
 * @property BulkReportPayload $payload
 * @property BulkReportResult $result
 */
class BulkReport extends MonitoredJob
{
    public const QUEUE_JOB_NAME = 'parts-bulk-report';

    public const LEVEL_DEFAULT = self::LEVEL_WITHOUT_RESTRICTIONS;

    public const TYPE_FINANCIALS = 'financials';

    public const QUEUE_NAME = 'reports';

    public const REPOSITORY_INTERFACE_NAME = BulkReportRepositoryInterface::class;

    /**
     * Payload accessor
     *
     * @param string|null $value
     * @return BulkReportPayload
     */
    public function getPayloadAttribute(?string $value)
    {
        return BulkReportPayload::from(json_decode($value ?? '', true));
    }
}
