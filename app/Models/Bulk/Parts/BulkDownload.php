<?php

namespace App\Models\Bulk\Parts;

use App\Models\Common\MonitoredJob;
use App\Models\Common\MonitoredJobResult;
use App\Repositories\Bulk\Parts\BulkDownloadRepositoryInterface;

/**
 * Class BulkDownload
 *
 * Represents a parts bulk download job
 *
 * @package App\Models\Bulk\Parts
 * @property string $status status if the csv file if still building or completed
 * @property int $progress csv build progress
 * @property BulkDownloadPayload $payload
 * @property MonitoredJobResult $result
 */
class BulkDownload extends MonitoredJob
{
    public const QUEUE_NAME = 'parts-export-new';

    public const QUEUE_JOB_NAME = 'parts-bulk-download';

    public const LEVEL_DEFAULT = self::LEVEL_BY_DEALER;

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
    public function getPayloadAttribute(?string $value)
    {
        return BulkDownloadPayload::from(json_decode($value ?? '', true));
    }
}

/**
 * @OA\Schema(
 *     schema="parts/bulkdownload",
 *     type="object",
 *     description="Represents a parts bulk download file job",
 *     properties={
 *         @OA\Property(property="token", type="string", description="the primary key value for this message"),
 *         @OA\Property(property="queue", type="string", description="the name of the queue"),
 *         @OA\Property(property="concurrency_level", type="string", description="the allowed concurrency level"),
 *         @OA\Property(property="dealer_id", type="integer", description="the dealer id who launched it"),
 *         @OA\Property(property="name", type="string", description="the key name of the job"),
 *         @OA\Property(property="status", type="string"),
 *         @OA\Property(property="progress", type="float", description="the dealer id who launched it"),
 *         @OA\Property(
 *                     property="payload",
 *                     description="data useful for handle the job",
 *                     type="object",
 *                     @OA\Property(property="export_file", type="string", description="location of the finished file")
 *        ),
 *        @OA\Property(
 *                     property="result",
 *                     type="object",
 *                     @OA\Property(property="message", type="string")
 *        ),
 *        @OA\Property(property="created_at", type="string", description="when the job was created"),
 *        @OA\Property(property="updated_at", type="string", description="when the job was last updated"),
 *        @OA\Property(property="finished_at", type="string", description="when the job was finished"),
 *     }
 * )
 */

