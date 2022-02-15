<?php

declare(strict_types=1);

namespace Tests\Integration\Jobs\Integration\CVR;

use App\Jobs\Integration\CVR\CvrSendFileJob;
use App\Models\Integration\CVR\CvrFile;
use App\Repositories\Integration\CVR\CvrFileRepositoryInterface;
use App\Services\Integration\CVR\CvrFileServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\Uuid;
use Tests\Integration\AbstractMonitoredJobsTest;
use Throwable;

/**
 * @covers \App\Jobs\Integration\CVR\CvrSendFileJob::handle
 * @group MonitoredJobs
 */
class CvrSendFileJobTest extends AbstractMonitoredJobsTest
{
    /**
     * @throws Throwable
     */
    public function testThrowsModelNotFoundException(): void
    {
        // Given I have a token from a non existing job
        $someToken = Uuid::uuid4()->toString();

        // And I have a queueable job specified by `CvrSendFileJob`
        $queueableJob = new CvrSendFileJob($someToken);

        // Then I expect to see an specific exception to be thrown
        $this->expectException(ModelNotFoundException::class);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage(sprintf('No query results for model [%s] %s', CvrFile::class, $someToken));

        // When I call handle method
        $queueableJob->handle(app(CvrFileRepositoryInterface::class), app(CvrFileServiceInterface::class));
    }
}
