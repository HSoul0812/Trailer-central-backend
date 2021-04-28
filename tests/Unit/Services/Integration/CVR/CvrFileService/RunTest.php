<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Integration\CVR\CvrFileService;

use App\Models\Integration\CVR\CvrFile;
use App\Models\Integration\CVR\CvrFilePayload;
use App\Services\Integration\CVR\CvrFileService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Exception;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * @covers \App\Services\Integration\CVR\CvrFileService::run
 * @group MonitoredJobs
 */
class RunTest extends TestCase
{
    use WithFaker;

    /**
     * @throws Exception  when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testWillThrowAnExceptionBecauseFileNotFound(): void
    {
        /** @var  MockInterface|LegacyMockInterface|CvrFileService $service */

        // Given I have the four dependencies for "CvrFileServiceDependencies" creation
        $dependencies = new CvrFileServiceDependencies();
        // And I have a well formed payload
        $payload = CvrFilePayload::from([]);
        // And I have a "CvrFile" for the monitored job
        $token = Uuid::uuid4()->toString();
        $job = new CvrFile([
            'dealer_id' => $this->faker->unique()->numberBetween(100, 50000),
            'token' => $token,
            'payload' => $payload->asArray(),
            'queue' => CvrFile::QUEUE_NAME,
            'concurrency_level' => CvrFile::LEVEL_DEFAULT,
            'name' => CvrFile::QUEUE_JOB_NAME
        ]);
        // And I know suddenly some exception could be thrown
        $exception = new FileNotFoundException(sprintf('File does not exist at path %s', '/tmp/gen-filename.gen'));

        // Then I expect that a log entry is stored
        $dependencies->loggerService->shouldReceive('info')->once();
        // And I expect that bulk repository "updateProgress" method is called once with certain arguments returning true
        $dependencies->fileRepository
            ->shouldReceive('updateProgress')
            ->once()
            ->with($job->token, 1)
            ->andReturnTrue();
        // And I expect that bulk repository "setFailed" method is called once with certain arguments returning true
        $dependencies->fileRepository
            ->shouldReceive('setFailed')
            ->once()
            ->with($job->token, ['message' => "Got exception: {$exception->getMessage()}"])
            ->andReturnTrue();
        // Then I expect that a log entry is stored
        $dependencies->loggerService->shouldReceive('error')->once();

        // Also I have a "CvrFileService" properly created
        $service = Mockery::mock(CvrFileService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // Then I expect that "send" is called once and will throw a known exception
        $service->shouldReceive('sendFile')->with($job)->once()->andThrow($exception);
        // And I expect to see an specific exception to be thrown
        $this->expectException(get_class($exception));
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($exception->getMessage());

        // When I call the run method
        $service->run($job);
    }

    /**
     * @throws Exception  when Uuid::uuid4()->toString() could not generate a uuid
     */
    public function testWillSendTheCvrFile(): void
    {
        /** @var  MockInterface|LegacyMockInterface|CvrFileService $service */

        // Given I have the four dependencies for "CvrFileServiceDependencies" creation
        $dependencies = new CvrFileServiceDependencies();
        // And I have a well formed payload
        $payload = CvrFilePayload::from([]);
        // And I have a "CvrFile" for the monitored job
        $token = Uuid::uuid4()->toString();
        $job = new CvrFile([
            'dealer_id' => $this->faker->unique()->numberBetween(100, 50000),
            'token' => $token,
            'payload' => $payload->asArray(),
            'queue' => CvrFile::QUEUE_NAME,
            'concurrency_level' => CvrFile::LEVEL_DEFAULT,
            'name' => CvrFile::QUEUE_JOB_NAME
        ]);

        // Then I expect that two log entries are stored
        $dependencies->loggerService->shouldReceive('info')->twice();
        // And I expect that bulk repository "updateProgress" method is called once with certain arguments returning true
        $dependencies->fileRepository
            ->shouldReceive('updateProgress')
            ->once()
            ->with($job->token, 1)
            ->andReturnTrue();
        // And I expect that bulk repository "setCompleted" method is called once with certain arguments returning true
        $dependencies->fileRepository
            ->shouldReceive('setCompleted')
            ->once()
            ->with($job->token)
            ->andReturnTrue();

        // Also I have a "CvrFileService" properly created
        $service = Mockery::mock(CvrFileService::class, $dependencies->getOrderedArguments())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // Then I expect that "send" is called once and will throw a known exception
        $service->shouldReceive('sendFile')->with($job)->once();

        // When I call the run method
        $service->run($job);
    }
}
