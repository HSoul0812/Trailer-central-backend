<?php

namespace Tests\Unit\Services\User\DealerIntegration;

use Mockery;

use Illuminate\Support\Facades\Mail;
use App\Mail\Integration\DealerIntegrationEmail;
use App\Services\User\DealerIntegrationService;

use Tests\TestCase;

use App\Models\User\User;
use App\Models\User\Integration\Integration;
use App\Models\User\Integration\DealerIntegration;

use App\Services\User\DealerIntegrationServiceInterface;
use App\Repositories\User\Integration\DealerIntegrationRepositoryInterface;

/**
 * Test for Tests\Unit\Services\User\DealerIntegration
 *
 * Class DealerIntegrationServiceTest
 * @package Tests\Unit\Services\User\DealerIntegration
 *
 * @coversDefaultClass \App\Services\User\DealerIntegrationService
 */
class DealerIntegrationServiceTest extends TestCase
{
    /**
     * @var DealerIntegrationRepositoryInterface
     */
    private $repository;

    /**
     * @var DealerIntegrationServiceInterface
     */
    private $service;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(DealerIntegrationRepositoryInterface::class);
        $this->app->instance(DealerIntegrationRepositoryInterface::class, $this->repository);

        $this->service = Mockery::mock(DealerIntegrationServiceInterface::class);
        $this->app->instance(DealerIntegrationServiceInterface::class, $this->service);
    }

    public function testUpdateDealerIntegration()
    {
        $dealer = $this->getEloquentMock(User::class);
        $integration = $this->getEloquentMock(Integration::class);
        $dealerIntegration = $this->getEloquentMock(DealerIntegration::class);

        $dealer->dealer_id = 1;
        $integration->integration_id = 1;

        $dealerIntegration->dealer_id = 1;
        $dealerIntegration->integration_id = 1;

        $dealerIntegration->shouldReceive('setRelation')->passthru();
        $dealerIntegration->shouldReceive('belongsTo')->passthru();
        $dealerIntegration->shouldReceive('dealer')->passthru();
        $dealerIntegration->shouldReceive('integration')->passthru();

        $this->service
            ->shouldReceive('update')
            ->with([
                'dealer_id' => $dealerIntegration->dealer_id,
                'integration_id' => $dealerIntegration->integration_id
            ])
            ->andReturn($dealerIntegration);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([
                'dealer_id' => $dealerIntegration->dealer_id,
                'integration_id' => $dealerIntegration->integration_id
            ])
            ->andReturn($dealerIntegration);

        Mail::shouldReceive('send')->once()->andReturnUsing(function ($message) {
            $message->build();
            $this->assertTrue($message->hasFrom(config('mail.from.address')));
            $this->assertTrue($message->hasTo(config('support.to.address')));
            $this->assertInstanceOf(DealerIntegrationEmail::class, $message);
        });

        $service = $this->app->make(DealerIntegrationService::class);

        $result = $service->update([
            'dealer_id' => $dealerIntegration->dealer_id,
            'integration_id' => $dealerIntegration->integration_id
        ]);

        $this->assertInstanceOf(DealerIntegration::class, $result);
    }

    public function testDeleteDealerIntegration()
    {
        $dealer = $this->getEloquentMock(User::class);
        $integration = $this->getEloquentMock(Integration::class);
        $dealerIntegration = $this->getEloquentMock(DealerIntegration::class);

        $dealer->dealer_id = 1;
        $integration->integration_id = 1;

        $dealerIntegration->dealer_id = 1;
        $dealerIntegration->integration_id = 1;

        $dealerIntegration->shouldReceive('setRelation')->passthru();
        $dealerIntegration->shouldReceive('belongsTo')->passthru();
        $dealerIntegration->shouldReceive('dealer')->passthru();
        $dealerIntegration->shouldReceive('integration')->passthru();

        $this->service
            ->shouldReceive('delete')
            ->with([
                'dealer_id' => $dealerIntegration->dealer_id,
                'integration_id' => $dealerIntegration->integration_id
            ])
            ->andReturn($dealerIntegration);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with([
                'dealer_id' => $dealerIntegration->dealer_id,
                'integration_id' => $dealerIntegration->integration_id
            ])
            ->andReturn($dealerIntegration);

        Mail::shouldReceive('send')->once()->andReturnUsing(function ($message) {
            $message->build();
            $this->assertTrue($message->hasFrom(config('mail.from.address')));
            $this->assertTrue($message->hasTo(config('support.to.address')));
            $this->assertInstanceOf(DealerIntegrationEmail::class, $message);
        });

        $service = $this->app->make(DealerIntegrationService::class);

        $result = $service->delete([
            'dealer_id' => $dealerIntegration->dealer_id,
            'integration_id' => $dealerIntegration->integration_id
        ]);

        $this->assertInstanceOf(DealerIntegration::class, $result);
    }
}
