<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Integration\CVR\CvrFileService;

use App\Models\Integration\CVR\CvrFile;
use App\Models\Integration\CVR\CvrFilePayload;
use App\Services\Integration\CVR\CvrFileService;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Exception;

/**
 * @covers \App\Services\Integration\CVR\CvrFileService::setup
 * @group MonitoredJobs
 */
class SetupTest extends TestCase
{
    use WithFaker;

    /**
     * @throws Exception
     */
    public function testWillCreateCvrFileJob(): void
    {
        /** @var MockInterface|LegacyMockInterface|CvrFileService $service */

        // Given I have the three required dependencies for "CvrFileServiceDependencies" creation
        $dependencies = new CvrFileServiceDependencies();
        // And I'm a dealer with a specific id
        $dealerId = $this->faker->unique()->numberBetween(100, 50000);
        // And I have a specific token from a cvr file monitored job
        $token = Uuid::uuid4()->toString();
        // And I have a specific payload
        $payload = CvrFilePayload::from([]);

        // Then I expect that a known "CvrFile" model is going to be returned
        $expectedCvrFile = new CvrFile([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => $payload->asArray(),
            'queue' => CvrFile::QUEUE_NAME,
            'concurrency_level' => CvrFile::LEVEL_DEFAULT,
            'name' => CvrFile::QUEUE_JOB_NAME
        ]);

        // And I expect that repository create method is called with certain arguments
        $dependencies->fileRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'dealer_id' => $dealerId,
                'token' => $token,
                'payload' => $payload->asArray(),
                'queue' => CvrFile::QUEUE_NAME,
                'concurrency_level' => CvrFile::LEVEL_DEFAULT,
                'name' => CvrFile::QUEUE_JOB_NAME
            ])
            ->andReturn($expectedCvrFile);

        // Also I have a "CvrFileService" properly created
        $service = new CvrFileService(
            $dependencies->fileRepository,
            $dependencies->loggerService,
            $dependencies->jobsRepository,
            $dependencies->cvrGeneratorService
        );

        // When I call the run method
        $cvrFile = $service->setup($dealerId, $payload, $token);

        // Then I expect to receive a new "CvrFile" model
        self::assertSame($expectedCvrFile, $cvrFile);
    }
}
