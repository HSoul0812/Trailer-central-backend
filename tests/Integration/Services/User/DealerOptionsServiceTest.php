<?php
namespace Tests\Integration\Services\User;

use App\Services\User\DealerOptionsServiceInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\database\seeds\Website\WebsiteSeeder;

/**
 * Test for App\Services\User\DealerOptionsService
 *
 * class DealerOptionsServiceTest
 * @package Tests\Integration\Services\User
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class DealerOptionsServiceTest extends \Tests\TestCase
{
    use DatabaseTransactions;

    /**
     * @var WebsiteSeeder
     */
    private $seeder;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->seeder = new WebsiteSeeder();
        $this->seeder->seed();
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        $this->seeder->cleanUp();
        parent::tearDown();
    }

    /**
     * @covers ::manageDealerSubscription
     *
     * @dataProvider validDataProviderForManageCrm
     *
     * @group DMS
     * @group DMS_DEALER
     *
     * @return void
     * @throws \Exception
     */
    public function testManageUserAccounts($subscription, $active) {
        $service = $this->getConcreteService();
        $service->manageDealerSubscription(
            $this->seeder->dealer->getKey(),
            (object) [
                'subscription' => $subscription,
                'active' => $active
            ]
        );
        $this->assertDatabaseHas('website_config', [
            'website_id' => $this->seeder->website->getKey(),
            'key' => 'general/user_accounts',
            'value' => $active
        ]);
    }

    /**
     * @return DealerOptionsServiceInterface
     */
    protected function getConcreteService(): DealerOptionsServiceInterface
    {
        return $this->app->make(DealerOptionsServiceInterface::class);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForManageCrm(): array
    {
        return [
            'Activate UserAccounts' => [
                'subscription' => 'user_accounts',
                'active' => 1
            ],
            'Deactivate UserAccounts' => [
                'subscription' => 'user_accounts',
                'active' => 0
            ]
        ];
    }
}
