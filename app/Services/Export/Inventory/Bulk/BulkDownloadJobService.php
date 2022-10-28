<?php

namespace App\Services\Export\Inventory\Bulk;

use App\Models\Bulk\Inventory\BulkDownload;
use App\Models\Bulk\Inventory\BulkDownloadPayload;
use App\Repositories\Bulk\Inventory\BulkDownloadRepositoryInterface;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Services\Common\AbstractMonitoredJobService;

class BulkDownloadJobService extends AbstractMonitoredJobService implements BulkDownloadJobServiceInterface
{
    /** @var BulkDownloadRepositoryInterface */
    private $bulkRepository;

    public function __construct(
        BulkDownloadRepositoryInterface $bulkRepository,
        MonitoredJobRepositoryInterface $monitoredJobsRepository
    )
    {
        parent::__construct($monitoredJobsRepository);

        $this->bulkRepository = $bulkRepository;
    }

    /**
     * @param int $dealerId
     * @param array|BulkDownloadPayload $payload
     * @param string|null $token
     * @return BulkDownload
     */
    public function setup(int $dealerId, $payload, ?string $token = null): BulkDownload
    {
        return $this->bulkRepository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => BulkDownload::QUEUE_NAME,
            'concurrency_level' => BulkDownload::LEVEL_DEFAULT,
            'name' => BulkDownload::QUEUE_JOB_NAME
        ]);
    }

    public function handler(string $outputType): BulkExporterJobServiceInterface
    {
        // when we have more export handlers like csv of excel, then we need to resolve it here
        return app(BulkPdfJobServiceInterface::class);
    }
}
