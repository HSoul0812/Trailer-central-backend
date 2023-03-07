<?php

namespace App\Models\Bulk\Inventory;

use App\Models\Common\MonitoredJob;
use App\Models\Common\MonitoredJobResult;
use App\Repositories\Bulk\Inventory\BulkDownloadRepositoryInterface;

/**
 * Represents an inventory bulk download job
 *
 * @property string $status status if the csv file if still building or completed
 * @property int $progress csv build progress
 * @property BulkDownloadPayload $payload
 * @property MonitoredJobResult $result
 */
class BulkDownload extends MonitoredJob
{
    public const QUEUE_NAME = 'inventory';

    public const QUEUE_JOB_NAME = 'inventory-process-bulk-download';

    public const LEVEL_DEFAULT = self::LEVEL_WITHOUT_RESTRICTIONS;

    /**
     * new and has not started assembling
     */
    public const STATUS_NEW = parent::STATUS_PENDING; // for backward compatibility

    /**
     * csv file was not created
     */
    public const STATUS_ERROR = parent::STATUS_FAILED; // for backward compatibility

    public const REPOSITORY_INTERFACE_NAME = BulkDownloadRepositoryInterface::class;

    /**
     * Payload accessor
     *
     * @param string|null $value
     * @return BulkDownloadPayload
     */
    public function getPayloadAttribute(?string $value): BulkDownloadPayload
    {
        return BulkDownloadPayload::from(json_decode($value ?? '', true));
    }

    public static function fromMonitoredJob(MonitoredJob $job): self
    {
        return unserialize(
            preg_replace(
                '/^O:\d+:"[^"]++"/',
                sprintf('O:%s:"%s"', strlen(__CLASS__), __CLASS__),
                serialize($job)
            ),
            [MonitoredJob::class]
        );
    }
}

