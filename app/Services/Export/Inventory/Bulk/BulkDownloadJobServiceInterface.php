<?php

namespace App\Services\Export\Inventory\Bulk;

use App\Models\Bulk\Inventory\BulkDownload;
use App\Models\Bulk\Inventory\BulkDownloadPayload;
use App\Services\Common\MonitoredJobServiceInterface;

interface BulkDownloadJobServiceInterface extends MonitoredJobServiceInterface
{
    /**
     * @param int $dealerId
     * @param BulkDownloadPayload|array $payload
     * @param string|null $token
     */
    public function setup(int $dealerId, $payload, ?string $token = null): BulkDownload;

    /**
     * @param BulkDownload $job
     * @return mixed
     */
    public function dispatch($job): void;

    /**
     * @param BulkDownload $job
     */
    public function dispatchNow($job): void;

    /**
     * Will return a valid exporter handler
     *
     * @param string $outputType
     * @return BulkExporterJobServiceInterface
     */
    public function handler(string $outputType): BulkExporterJobServiceInterface;
}
