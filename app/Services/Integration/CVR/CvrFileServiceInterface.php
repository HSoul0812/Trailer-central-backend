<?php

declare(strict_types=1);

namespace App\Services\Integration\CVR;

use App\Models\Integration\CVR\CvrFile;
use App\Models\Integration\CVR\CvrFilePayload;
use App\Services\Common\MonitoredJobServiceInterface;
use App\Services\Common\RunnableJobServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

interface CvrFileServiceInterface extends MonitoredJobServiceInterface, RunnableJobServiceInterface
{
    /**
     * @param int $dealerId
     * @param CvrFilePayload|array $payload
     * @param string|null $token
     * @return CvrFile
     */
    public function setup(int $dealerId, $payload, ?string $token = null): CvrFile;

    /**
     * @param CvrFile $job
     * @return mixed
     */
    public function dispatch($job): void;

    /**
     * @param CvrFile $job
     */
    public function dispatchNow($job): void;

    /**
     * @param string $filename CVR zipped filepath
     * @throws FileNotFoundException when the file was not found
     */
    public function send(string $filename): void;
}
