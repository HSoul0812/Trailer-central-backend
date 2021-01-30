<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Models\Common\MonitoredJob;

interface RunnableJobServiceInterface
{
    /**
     * Run the service based on the job instance
     *
     * @param MonitoredJob $job
     * @return mixed
     */
    public function run($job);
}
