<?php

namespace Tests\Unit\Services\User\DealerOptionService;

use App\Models\User\DealerAdminSetting;
use App\Models\User\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\DealerOptionsService;
use Mockery;
use Tests\TestCase;
use Mockery\LegacyMockInterface;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Test for App\Services\User\DealerOptionsService
 *
 * class ManageCdkTest
 * @package Tests\Unit\Services\User\DealerOptionService
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class ManageCdkTest extends TestCase
{

    /**
     * @var LegacyMockInterface|UserRepositoryInterface
     */
    private $userRepository;

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepository);

        $this->user = $this->getEloquentMock(User::class);
        $this->app->instance(User::class, $this->user);
    }

    /**
     * @covers ::manageCdk
     *
     * @dataProvider validDataProviderForManageCdk
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageCdk($dealerId, $sourceId, $active)
    {
        $adminSetting = $this->getEloquentMock(DealerAdminSetting::class);
        $hasMany = \Mockery::mock(HasMany::class)->shouldAllowMockingProtectedMethods();

        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once()

            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($this->user);

        $this->user
            ->shouldReceive('adminSettings')
            ->once()
            ->andReturn($hasMany);

        $hasMany
            ->shouldReceive('where')
            ->with([
                'setting' => 'website_leads_cdk_source_id'
            ])
            ->once()
            ->andReturnSelf()

            ->shouldReceive('firstOr')
            ->once()
            ->andReturn($adminSetting);

        $adminSetting
            ->shouldReceive('update')
            ->once()
            ->andReturn(true);

        $this->userRepository
            ->shouldReceive('commitTransaction')
            ->once();

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageCdk($dealerId, $active, $sourceId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::manageCdk
     *
     * @dataProvider invalidDataProviderForManageCdk
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testActivateCdkWithoutSourceId($dealerId, $sourceId, $active)
    {
        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once()

            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($this->user)

            ->shouldReceive('rollbackTransaction')
            ->once();

        Log::shouldReceive('error');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Source Id is required when activating CDK.');

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageCdk($dealerId, $active, $sourceId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::manageCdk
     *
     * @dataProvider invalidValueTypesDataProviderForManageCdk
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testManageCdkWithInvalidValueTypes($dealerId, $sourceId, $active)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageCdk($dealerId, $active, $sourceId);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForManageCdk(): array
    {
        return [
            'Activate CDK' => [
                'dealer_id' => 1001,
                'source_id' => 'testing123',
                'active' => 1
            ],
            'Deactivate CDK' => [
                'dealer_id' => 1001,
                'source_id' => 'testing123',
                'active' => 0
            ],
            'Deactivate CDK without sourceId' => [
                'dealer_id' => 1001,
                'source_id' => '',
                'active' => 0
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function invalidDataProviderForManageCdk(): array
    {
        return [
            'Activate CDK with empty SourceId' => [
                'dealer_id' => 1001,
                'source_id' => '',
                'active' => 1
            ],
            'Activate CDK with null SourceId' => [
                'dealer_id' => 1001,
                'source_id' => null,
                'active' => 1
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function invalidValueTypesDataProviderForManageCdk(): array
    {
        $badDealers = ['TESTING', null, ''];
        $dealerId = $badDealers[array_rand($badDealers)];

        return [
            'Activate CDK with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'source_id' => 'testing123',
                'active' => 1
            ],
            'Deactivate CDK with invalid dealer id' => [
                'dealer_id' => $dealerId,
                'source_id' => '',
                'active' => 0
            ]
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
