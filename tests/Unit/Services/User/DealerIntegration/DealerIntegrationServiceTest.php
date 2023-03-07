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
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(DealerIntegrationRepositoryInterface::class);
        $this->app->instance(DealerIntegrationRepositoryInterface::class, $this->repository);
    }

    /**
     * @covers ::update
     * @group Integrations
     * @dataProvider dealerIntegrationDataProvider
     */
    public function testUpdateDealerIntegration($dealerId, $integrationId)
    {
        Mail::fake();

        $dealer = $this->getEloquentMock(User::class);
        $integration = $this->getEloquentMock(Integration::class);
        $dealerIntegration = $this->getEloquentMock(DealerIntegration::class);

        $dealer->dealer_id = $dealerId;
        $integration->integration_id = $integrationId;

        $dealerIntegration->dealer_id = $dealerId;
        $dealerIntegration->integration_id = $integrationId;

        $this->initBelongsToRelation($dealerIntegration, 'dealer', $dealer);
        $this->initBelongsToRelation($dealerIntegration, 'integration', $integration);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([
                'dealer_id' => $dealerIntegration->dealer_id,
                'integration_id' => $dealerIntegration->integration_id
            ])
            ->andReturn($dealerIntegration);

        $service = $this->app->make(DealerIntegrationService::class);

        $result = $service->update([
            'dealer_id' => $dealerIntegration->dealer_id,
            'integration_id' => $dealerIntegration->integration_id
        ]);

        Mail::assertSent(DealerIntegrationEmail::class, function ($mail) {
            $mail->build();

            return $mail->hasTo(config('support.to.address')) &&
                   $mail->hasFrom(config('mail.from.address'));
        });

        $this->assertInstanceOf(DealerIntegration::class, $result);
    }

    /**
     * @covers ::delete
     * @group Integrations
     * @dataProvider dealerIntegrationDataProvider
     */
    public function testDeleteDealerIntegration($dealerId, $integrationId)
    {
        Mail::fake();

        $dealer = $this->getEloquentMock(User::class);
        $integration = $this->getEloquentMock(Integration::class);
        $dealerIntegration = $this->getEloquentMock(DealerIntegration::class);

        $dealer->dealer_id = $dealerId;
        $integration->integration_id = $integrationId;

        $dealerIntegration->dealer_id = $dealerId;
        $dealerIntegration->integration_id = $integrationId;

        $this->initBelongsToRelation($dealerIntegration, 'dealer', $dealer);
        $this->initBelongsToRelation($dealerIntegration, 'integration', $integration);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with([
                'dealer_id' => $dealerIntegration->dealer_id,
                'integration_id' => $dealerIntegration->integration_id
            ])
            ->andReturn($dealerIntegration);

        $service = $this->app->make(DealerIntegrationService::class);

        $result = $service->delete([
            'dealer_id' => $dealerIntegration->dealer_id,
            'integration_id' => $dealerIntegration->integration_id
        ]);

        Mail::assertSent(DealerIntegrationEmail::class, function ($mail) {
            $mail->build();

            return $mail->hasTo(config('support.to.address')) &&
                   $mail->hasFrom(config('mail.from.address'));
        });

        $this->assertInstanceOf(DealerIntegration::class, $result);
    }

    public function dealerIntegrationDataProvider(): array
    {
        return [
            [
                'dealer_id' => 1,
                'integration_id' => 1,
            ]
        ];
    }
}
