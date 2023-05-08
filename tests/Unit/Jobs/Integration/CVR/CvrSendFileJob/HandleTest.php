<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Integration\CVR\CvrSendFileJob;

use App\Jobs\Integration\CVR\CvrSendFileJob;
use App\Models\Integration\CVR\CvrFile;
use App\Repositories\Integration\CVR\CvrFileRepository;
use App\Services\Integration\CVR\CvrFileService;
use App\Services\Integration\CVR\CvrFileServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Log;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 *
 * @covers \App\Jobs\Integration\CVR\CvrSendFileJob::handle
 * @group MonitoredJobs
 */
class HandleTest extends TestCase
{
    use WithFaker;

    /**
     * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testWillThrowAnExceptionBecauseModelNotFoundException(): void
    {
        /* @var  MockInterface|LegacyMockInterface|CvrFileService $job */
        /* @var CvrSendFileJob $job */

        // Given I have a token of a non exists monitored job
        $someToken = Uuid::uuid4()->toString();
        // And I have a "CvrSendFileJob" properly created
        $job = Mockery::mock(CvrSendFileJob::class, [$someToken])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        // And I know what exception will be thrown
        $exception = new ModelNotFoundException(sprintf('No query results for model [%s] %s', CvrFile::class, $someToken));

        // And I the two required dependencies for "CvrSendFileJob" creation
        $fileRepository = Mockery::mock(CvrFileRepository::class);
        $fileSenderService = Mockery::mock(CvrFileServiceInterface::class);

        // Then I expect that repository method "findByToken" is called once with certain
        // arguments and will throw a known exception
        $fileRepository->shouldReceive('findByToken')
            ->once()
            ->with($someToken)
            ->andThrow($exception);

        // Then I expect that a log entry is stored
        Log::shouldReceive('error')
            ->once()
            ->with(
                sprintf(
                    'Error running job for sending the CVR file: [token: %s, exception: %s]',
                    $someToken,
                    $exception->getMessage()
                )
            );

        // Then I expect to see an specific exception to be thrown
        $this->expectException(get_class($exception));
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($exception->getMessage());

        // When I call the "handle" method
        $job->handle($fileRepository, $fileSenderService);
    }

    /**
     * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testWillRunAsExpected(): void
    {
        /* @var  MockInterface|LegacyMockInterface|CvrFileService $cvrFile */
        /* @var CvrSendFileJob $job */

        // Given I have a token of a non exists monitored job
        $token = Uuid::uuid4()->toString();
        // And I have a "CvrSendFileJob" properly created
        $job = Mockery::mock(CvrSendFileJob::class, [$token])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // And I the two required dependencies for "CvrSendFileJob" creation
        $fileRepository = Mockery::mock(CvrFileRepository::class);
        $fileSenderService = Mockery::mock(CvrFileServiceInterface::class);

        // Then I expect that a known "CvrFile" model is going to be returned
        $cvrFile = new CvrFile([
            'dealer_id' => $this->faker->unique()->numberBetween(100, 50000),
            'token' => $token,
            'payload' => ['document' => UploadedFile::fake()->create('some-filename.zip', 7800)],
            'queue' => CvrFile::QUEUE_NAME,
            'concurrency_level' => CvrFile::LEVEL_DEFAULT,
            'name' => CvrFile::QUEUE_JOB_NAME
        ]);

        // And I expect that repository method "findByToken" is called once with certain
        // arguments and will returns a known "CvrFile" model
        $fileRepository->shouldReceive('findByToken')
            ->once()
            ->with($token)
            ->andReturn($cvrFile);
        // And I expect that service method "run" method is called once with certain arguments
        $fileSenderService->shouldReceive('run')
            ->once()
            ->with($cvrFile);

        // When I call the "handle" method
        $job->handle($fileRepository, $fileSenderService);
    }
}
